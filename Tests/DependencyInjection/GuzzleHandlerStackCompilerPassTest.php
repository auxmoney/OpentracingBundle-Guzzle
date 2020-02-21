<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\DependencyInjection;

use Auxmoney\OpentracingGuzzleBundle\DependencyInjection\GuzzleHandlerStackCompilerPass;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

class GuzzleHandlerStackCompilerPassTest extends TestCase
{
    /** @var GuzzleHandlerStackCompilerPass */
    private $subject;

    public function setUp()
    {
        parent::setUp();

        $this->subject = new GuzzleHandlerStackCompilerPass();
    }

    public function testProcessNoClientDefinitions(): void
    {
        $noClientDefinition = $this->prophesize(Definition::class);
        $noClientDefinition->getClass()->willReturn(Definition::class);
        $container = new ContainerBuilder();
        $container->addDefinitions(['noclient' => $noClientDefinition->reveal()]);

        $noClientDefinition->setArguments(Argument::any())->shouldNotBeCalled();

        $this->subject->process($container);
    }

    /**
     * @dataProvider provideHandlerConfigWithoutHandler
     */
    public function testProcessHandlerConfigWithoutHandler(?array $arguments): void
    {
        $noClientDefinition = $this->prophesize(Definition::class);
        $noClientDefinition->getClass()->willReturn(Definition::class);
        $clientDefinition = $this->prophesize(Definition::class);
        $clientDefinition->getClass()->willReturn(Client::class);
        $container = new ContainerBuilder();
        $container->addDefinitions(['noclient' => $noClientDefinition->reveal(), 'client' => $clientDefinition->reveal()]);

        $clientDefinition->getArguments()->willReturn($arguments);

        $noClientDefinition->setArguments(Argument::any())->shouldNotBeCalled();
        $clientDefinition->setArguments(Argument::that(function ($config) {
            return is_array($config)
                && isset($config[0]['handler'])
                && $config[0]['handler'] instanceof Reference
                && (string) $config[0]['handler'] === 'auxmoney_opentracing.guzzlehttp.default.handlerstack';
        }))->shouldBeCalled();

        $this->subject->process($container);
    }

    public function provideHandlerConfigWithoutHandler(): array
    {
        return [
            ['no config' => [[]]],
            ['config indexed by number' => [0 => ['verify' => false]]],
            ['config indexed by name' => ['$config' => ['verify' => false]]],
        ];
    }

    /**
     * @dataProvider provideHandlerConfigWithHandler
     */
    public function testProcessHandlerConfigWithHandler(?array $arguments): void
    {
        $noClientDefinition = $this->prophesize(Definition::class);
        $noClientDefinition->getClass()->willReturn(Definition::class);
        $clientDefinition = $this->prophesize(Definition::class);
        $clientDefinition->getClass()->willReturn(Client::class);
        $handlerDefinition = new Definition(HandlerStack::class);
        $container = new ContainerBuilder();
        $container->addDefinitions(
            [
                'noclient' => $noClientDefinition->reveal(),
                'client' => $clientDefinition->reveal(),
                'handlerServiceName' => $handlerDefinition
            ]
        );

        $clientDefinition->getArguments()->willReturn($arguments);

        $noClientDefinition->setArguments(Argument::any())->shouldNotBeCalled();
        $noClientDefinition->setArguments(Argument::any())->shouldNotBeCalled();
        $clientDefinition->addTag('auxmoney_opentracing.enabled')->shouldBeCalled();

        $this->subject->process($container);

        self::assertCount(2, $handlerDefinition->getMethodCalls());
    }

    /**
     * @dataProvider provideHandlerConfigWithHandler
     */
    public function testProcessHandlerConfigWithHandlerMultipleClients(?array $arguments): void
    {
        $noClientDefinition = $this->prophesize(Definition::class);
        $noClientDefinition->getClass()->willReturn(Definition::class);
        $clientDefinition = $this->prophesize(Definition::class);
        $clientDefinition->getClass()->willReturn(Client::class);
        $otherClientDefinition = $this->prophesize(Definition::class);
        $otherClientDefinition->getClass()->willReturn(Client::class);
        $handlerDefinition = new Definition(HandlerStack::class);
        $container = new ContainerBuilder();
        $container->addDefinitions(
            [
                'noclient' => $noClientDefinition->reveal(),
                'client' => $clientDefinition->reveal(),
                'otherClient' => $otherClientDefinition->reveal(),
                'handlerServiceName' => $handlerDefinition
            ]
        );

        $clientDefinition->getArguments()->willReturn($arguments);
        $otherClientDefinition->getArguments()->willReturn($arguments);

        $noClientDefinition->setArguments(Argument::any())->shouldNotBeCalled();
        $noClientDefinition->setArguments(Argument::any())->shouldNotBeCalled();
        $clientDefinition->addTag('auxmoney_opentracing.enabled')->shouldBeCalled();
        $otherClientDefinition->addTag('auxmoney_opentracing.enabled')->shouldBeCalled();

        $this->subject->process($container);

        self::assertCount(2, $handlerDefinition->getMethodCalls());
    }

    public function provideHandlerConfigWithHandler(): array
    {
        return [
            ['config indexed by number' => [0 => ['verify' => false, 'handler' => new Reference('handlerServiceName')]]],
            ['config indexed by name' => ['$config' => ['verify' => false, 'handler' => new Reference('handlerServiceName')]]],
        ];
    }

    public function testProcessHandlerConfigWithHandlerButNoHandlerStack(): void
    {
        $noClientDefinition = $this->prophesize(Definition::class);
        $noClientDefinition->getClass()->willReturn(Definition::class);
        $clientDefinition = $this->prophesize(Definition::class);
        $clientDefinition->getClass()->willReturn(Client::class);
        $handlerDefinition = $this->prophesize(Definition::class);
        $handlerDefinition->getClass()->willReturn(Definition::class);
        $container = new ContainerBuilder();
        $container->addDefinitions(
            [
                'noclient' => $noClientDefinition->reveal(),
                'client' => $clientDefinition->reveal(),
                'handlerServiceName' => $handlerDefinition->reveal()
            ]
        );

        $clientDefinition->getArguments()->willReturn([0 => ['verify' => false, 'handler' => new Reference('handlerServiceName')]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/you cannot use the guzzle plugin/');
        $noClientDefinition->setArguments(Argument::any())->shouldNotBeCalled();
        $clientDefinition->setArguments(Argument::any())->shouldNotBeCalled();
        $clientDefinition->addTag('auxmoney_opentracing.enabled')->shouldNotBeCalled();
        $handlerDefinition->addMethodCall('push', Argument::any())->shouldNotBeCalled();

        $this->subject->process($container);
    }
}
