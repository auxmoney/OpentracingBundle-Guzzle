<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\DependencyInjection;

use Auxmoney\OpentracingGuzzleBundle\DependencyInjection\GuzzleHandlerStackCompilerPass;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class GuzzleHandlerStackCompilerPassTest extends TestCase
{
    use ProphecyTrait;

    /** @var GuzzleHandlerStackCompilerPass */
    private $subject;

    public function setUp(): void
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

    public function testProcessHandlerConfigWithoutHandler(): void
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

    public function testProcessHandlerConfigWithHandler(): void
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

    public function testProcessHandlerConfigWithHandlerMultipleClients(): void
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
}
