<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\Decorator;

use Auxmoney\OpentracingBundle\Internal\Decorator\RequestSpanning;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Auxmoney\OpentracingGuzzleBundle\Decorator\GuzzleClientFactoryDecorator;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleRequestSpanning;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleTracingHeaderInjection;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

class GuzzleClientFactoryDecoratorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDecorateClient(): void
    {
        $client = $this->prophesize(Client::class);
        $handlerStack = $this->prophesize(HandlerStack::class);
        $requestSpanning = $this->prophesize(RequestSpanning::class);
        $tracing = $this->prophesize(Tracing::class);

        $client->getConfig("handler")->willReturn($handlerStack->reveal());

        $spanning = new GuzzleRequestSpanning($requestSpanning->reveal(), $tracing->reveal());
        $headerInjection = new GuzzleTracingHeaderInjection($tracing->reveal());

        $handlerStack->push($spanning)->shouldBeCalled();
        $handlerStack->push($headerInjection)->shouldBeCalled();

        $clientReturned = GuzzleClientFactoryDecorator::decorate($client->reveal(), $spanning, $headerInjection);

        self::assertSame($client->reveal(), $clientReturned);
    }

    public function testDecorateNoClient(): void
    {
        $client = $this->prophesize(Client::class);
        $requestSpanning = $this->prophesize(RequestSpanning::class);
        $tracing = $this->prophesize(Tracing::class);

        $client->getConfig("handler")->willReturn("not a handler stack");

        $spanning = new GuzzleRequestSpanning($requestSpanning->reveal(), $tracing->reveal());
        $headerInjection = new GuzzleTracingHeaderInjection($tracing->reveal());

        $clientReturned = GuzzleClientFactoryDecorator::decorate($client->reveal(), $spanning, $headerInjection);

        self::assertSame($client->reveal(), $clientReturned);
    }
}
