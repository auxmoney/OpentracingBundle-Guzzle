<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\Functional;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use function JmesPath\search as jmesSearch;

class FunctionalTest extends TestCase
{
    public function testNestedSpansAndHeaderPropagation(): void
    {
        $p = new Process(['symfony', 'php', 'build/testproject/bin/console', 'test:guzzle']);
        $p->mustRun();
        $output = $p->getOutput();
        $traceId = substr($output, 0, strpos($output, ':'));
        self::assertNotEmpty($traceId);

        $spans = $this->getSpansFromJaegerAPI($traceId);
        self::assertCount(5, $spans);

        $traceAsYAML = $this->getTraceAsYAML($spans);
        self::assertSame(<<<EOT
operationName: 'test:guzzle'
tags:
  -
    key: command.exit-code
    value: 0
children:
  -
    operationName: 'sending HTTP request'
    tags:
      -
        key: http.method
        value: GET
      -
        key: http.url
        value: 'http://localhost:8000/'
      -
        key: http.status_code
        value: 200
    children:
      -
        operationName: 'http://localhost:8000/'
        tags:
          -
            key: http.method
            value: GET
          -
            key: http.url
            value: 'http://localhost:8000/'
        children:
          -
            operationName: 'App\Controller\TestController::index'
            tags:
              -
                key: http.status_code
                value: 200
            children:
              -
                operationName: 'sending HTTP request'
                tags:
                  -
                    key: http.method
                    value: GET
                  -
                    key: http.url
                    value: 'https://github.com/auxmoney/OpentracingBundle-Guzzle'
                  -
                    key: http.status_code
                    value: 200

EOT
            , $traceAsYAML);
    }

    public function setUp() {
        parent::setUp();

        $p = new Process(['docker', 'start', 'jaeger']);
        $p->mustRun();

        sleep(3);
    }

    protected function tearDown() {
        parent::tearDown();

        $p = new Process(['docker', 'stop', 'jaeger']);
        $p->mustRun();
    }

    private function getSpansFromJaegerAPI(string $traceId): array
    {
        $client = new Client();
        $response = $client->get(sprintf('http://localhost:16686/api/traces/%s?raw=true', $traceId));
        $contents = json_decode($response->getBody()->getContents(), true);
        return jmesSearch('data[0].spans', $contents);
    }

    private function getTraceAsYAML($spans): string
    {
        $spanData = jmesSearch(
            '[].{operationName: operationName, spanID: spanID, references: references, tags: tags[?key==\'http.status_code\' || key==\'command.exit-code\' || key==\'http.url\' || key==\'http.method\'].{key: key, value: value}}',
            $spans
        );
        $nodes = [];
        foreach ($spanData as $data) {
            $node = new stdClass();
            $node->operationName = $data['operationName'];
            $node->tags = $data['tags'];
            $node->childOf = $data['references'][0]['spanID'] ?? null;
            $nodes[$data['spanID']] = $node;
        }

        $rootNode = null;
        foreach ($nodes as $node) {
            if ($node->childOf) {
                $nodes[$node->childOf]->children[] = $node;
            } else {
                $rootNode = $node;
            }
            unset($node->childOf);
        }
        return Yaml::dump($rootNode, 1024, 2, Yaml::DUMP_OBJECT_AS_MAP);
    }
}
