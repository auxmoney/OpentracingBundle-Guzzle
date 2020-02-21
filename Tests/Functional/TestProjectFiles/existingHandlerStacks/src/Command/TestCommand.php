<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use const OpenTracing\Formats\TEXT_MAP;

class TestCommand extends Command
{
    private $client;
    private $opentracing;

    public function __construct(ClientInterface $client, Opentracing $opentracing)
    {
        parent::__construct('test:guzzle');
        $this->setDescription('some fancy command description');
        $this->client = $client;
        $this->opentracing = $opentracing;
    }

    /**
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $request = new Request('GET', '/');
        $this->client->send($request)->getBody()->getContents();

        $carrier = [];
        $this->opentracing->getTracerInstance()->inject($this->opentracing->getTracerInstance()->getActiveSpan()->getContext(), TEXT_MAP, $carrier);
        $output->writeln(current($carrier));
        return 0;
    }
}
