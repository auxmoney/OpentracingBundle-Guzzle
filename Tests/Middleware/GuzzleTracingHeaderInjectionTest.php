<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundleGuzzle\Tests\Middleware;

use Auxmoney\OpentracingBundleGuzzle\Middleware\GuzzleTracingHeaderInjection;
use Auxmoney\OpentracingBundleGuzzle\Service\Tracing;
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
        $handler = $subject(function ($request, $options) {
            return $request;
        });

        self::assertSame($injectedRequest->reveal(), $handler($originalRequest->reveal(), []));
    }
}
