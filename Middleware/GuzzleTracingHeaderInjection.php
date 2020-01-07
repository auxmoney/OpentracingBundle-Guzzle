<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundleGuzzle\Middleware;

use Auxmoney\OpentracingBundle\Service\Tracing;
use Psr\Http\Message\RequestInterface;

final class GuzzleTracingHeaderInjection
{
    private $tracingService;

    public function __construct(Tracing $tracingService)
    {
        $this->tracingService = $tracingService;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $this->tracingService->injectTracingHeaders($request);
            return $handler($request, $options);
        };
    }
}
