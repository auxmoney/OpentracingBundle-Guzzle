<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Decorator;

use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleRequestSpanning;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleTracingHeaderInjection;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

final class GuzzleClientFactoryDecorator
{
    public static function decorate(
        Client $client,
        GuzzleRequestSpanning $spanning,
        GuzzleTracingHeaderInjection $headerInjection
    ): Client {
        $handler = $client->getConfig("handler");

        if ($handler instanceof HandlerStack) {
            $handler->push($spanning);
            $handler->push($headerInjection);
        }

        return $client;
    }
}
