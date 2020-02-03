<?php

declare(strict_types=1);

namespace App\Controller;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestController extends AbstractController
{
    /**
     * @throws GuzzleException
     */
    public function index(ClientInterface $client) {
        $request = new Request('GET', 'https://github.com/auxmoney/OpentracingBundle-Guzzle');
        $contents = $client->send($request)->getBody()->getContents();
        return new JsonResponse(['nested' => true, 'contentsLength' => strlen($contents)]);
    }
}
