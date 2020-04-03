<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Middleware;

use Auxmoney\OpentracingBundle\Internal\Decorator\RequestSpanning;
use Auxmoney\OpentracingBundle\Service\Tracing;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class GuzzleRequestSpanning
{
    private $requestSpanning;
    private $tracing;

    public function __construct(RequestSpanning $requestSpanning, Tracing $tracing)
    {
        $this->requestSpanning = $requestSpanning;
        $this->tracing = $tracing;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $this->requestSpanning->start($request->getMethod(), $request->getUri()->__toString());

            /** @var PromiseInterface $promise */
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) {
                    $this->onFulfilled($response);
                    return $response;
                },
                function (RequestException $exception) {
                    $this->onRejected($exception);
                    throw $exception;
                }
            );
        };
    }

    private function onFulfilled(ResponseInterface $response): void
    {
        $this->requestSpanning->finish($response->getStatusCode());
        $this->tracing->finishActiveSpan();
    }

    private function onRejected(RequestException $exception): void
    {
        $this->tracing->logInActiveSpan(
            [
                'event' => 'error',
                'error.kind' => 'Exception',
                'error.object' => get_class($exception),
                'message' => $exception->getMessage(),
                'stack' => $exception->getTraceAsString(),
            ]
        );
        if ($exception->hasResponse()) {
            $this->requestSpanning->finish($exception->getResponse()->getStatusCode());
        }
        $this->tracing->finishActiveSpan();
    }
}
