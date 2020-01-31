<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\Middleware;

use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleTracingHeaderInjection;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class GuzzleTracingHeaderInjectionTest extends TestCase
{
    private $tracingService;

    public function setUp()
    {
        parent::setUp();
        $this->tracingService = $this->prophesize(Tracing::class);
    }

    public function testInvoke(): void
    {
        $originalRequest = $this->prophesize(RequestInterface::class);
        $injectedRequest = $this->prophesize(RequestInterface::class);

        $this->tracingService->injectTracingHeaders($originalRequest->reveal())->shouldBeCalled()->willReturn($injectedRequest->reveal());

        $subject = new GuzzleTracingHeaderInjection($this->tracingService->reveal());
        $handler = $subject(function ($request) {
            return $request;
        });

        self::assertSame($injectedRequest->reveal(), $handler($originalRequest->reveal(), []));
    }
}
