<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\Middleware;

use Auxmoney\OpentracingBundle\Internal\Decorator\RequestSpanning;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleRequestSpanning;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

class GuzzleRequestSpanningTest extends TestCase
{
    private $requestSpanning;
    private $tracing;

    public function setUp(): void
    {
        parent::setUp();
        $this->requestSpanning = $this->prophesize(RequestSpanning::class);
        $this->tracing = $this->prophesize(Tracing::class);
    }

    public function test__invokeFulfilled(): void
    {
        $this->requestSpanning->start('GET', '/foo-uri')->shouldBeCalled();
        $this->requestSpanning->finish(201)->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan('auxmoney-opentracing-bundle.span-origin', 'guzzle:request')->shouldBeCalled();
        $this->tracing->finishActiveSpan()->shouldBeCalled();

        $subject = new GuzzleRequestSpanning($this->requestSpanning->reveal(), $this->tracing->reveal());

        $handler = $subject(function () { // function($request, $options)
            return new FulfilledPromise(new Response(201, [], 'some body'));
        });

        /** @var Promise $promise */
        $promise = $handler(new Request('GET', '/foo-uri'), []);
        /** @var ResponseInterface $response */
        $response = $promise->wait();

        self::assertSame('some body', $response->getBody()->getContents());
        self::assertSame(201, $response->getStatusCode());
    }

    public function test__invokeRejected(): void
    {
        $this->expectException(RequestException::class);

        $request = new Request('GET', '/foo-uri');
        $exception =  new RequestException(
            'An error occured',
            $request
        );

        $this->requestSpanning->start('GET', '/foo-uri')->shouldBeCalled();
        $this->requestSpanning->finish(Argument::any())->shouldNotBeCalled();
        $this->tracing->logInActiveSpan(Argument::is([
            'event' => 'error',
            'error.kind' => 'Exception',
            'error.object' => RequestException::class,
            'message' => $exception->getMessage(),
            'stack' => $exception->getTraceAsString(),
        ]))->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan('auxmoney-opentracing-bundle.span-origin', 'guzzle:request')->shouldBeCalled();
        $this->tracing->finishActiveSpan()->shouldBeCalled();

        $subject = new GuzzleRequestSpanning($this->requestSpanning->reveal(), $this->tracing->reveal());

        $handler = $subject(function () use ($exception) { // function($request, $options)
            return new RejectedPromise($exception);
        });

        /** @var Promise $promise */
        $promise = $handler($request, []);
        $promise->wait();
    }

    public function test__invokeRejectedWithAnExceptionThatContainsResponse(): void
    {
        $this->expectException(RequestException::class);

        $request = new Request('GET', '/foo-uri');
        $exception =  new RequestException(
            'An error occured',
            $request,
            new Response(503)
        );

        $this->requestSpanning->start('GET', '/foo-uri')->shouldBeCalled();
        $this->requestSpanning->finish(Argument::is(503))->shouldBeCalled();
        $this->tracing->logInActiveSpan(Argument::is([
            'event' => 'error',
            'error.kind' => 'Exception',
            'error.object' => RequestException::class,
            'message' => $exception->getMessage(),
            'stack' => $exception->getTraceAsString(),
        ]))->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan('auxmoney-opentracing-bundle.span-origin', 'guzzle:request')->shouldBeCalled();
        $this->tracing->finishActiveSpan()->shouldBeCalled();

        $subject = new GuzzleRequestSpanning($this->requestSpanning->reveal(), $this->tracing->reveal());
        $request = new Request('GET', '/foo-uri');

        $handler = $subject(function () use ($exception) { // function($request, $options)
            return new RejectedPromise($exception);
        });

        /** @var Promise $promise */
        $promise = $handler($request, []);
        $promise->wait();
    }
}
