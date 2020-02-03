<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Http\Message\RequestInterface;

class TestHandler
{
    public function __invoke()
    {
        $header = 'a';
        $value = 'b';
        return function (
            RequestInterface $request
        ) use ($header, $value) {
            return $request->withHeader($header, $value);
        };
    }
}
