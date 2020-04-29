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

        self::assertCount(3, $container->getDefinitions());

        $this->subject->process($container);

        self::assertCount(4, $container->getDefinitions());
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

        self::assertCount(4, $container->getDefinitions());

        $this->subject->process($container);

        self::assertCount(5, $container->getDefinitions());
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

        self::assertCount(5, $container->getDefinitions());

        $this->subject->process($container);

        self::assertCount(7, $container->getDefinitions());
    }

    public function provideHandlerConfigWithHandler(): array
    {
        return [
            ['config indexed by number' => [0 => ['verify' => false, 'handler' => new Reference('handlerServiceName')]]],
            ['config indexed by name' => ['$config' => ['verify' => false, 'handler' => new Reference('handlerServiceName')]]],
        ];
    }
}
