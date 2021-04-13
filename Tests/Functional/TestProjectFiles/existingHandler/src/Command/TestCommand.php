<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\TracingId;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    private $client;
    private $tracingId;

    public function __construct(ClientInterface $client, TracingId $tracingId)
    {
        parent::__construct('test:guzzle');
        $this->setDescription('some fancy command description');
        $this->client = $client;
        $this->tracingId = $tracingId;
    }

    /**
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $request = new Request('GET', '/');
        $this->client->send($request)->getBody()->getContents();

        $output->writeln($this->tracingId->getAsString());
        return 0;
    }
}
