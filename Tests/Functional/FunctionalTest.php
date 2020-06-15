<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\Functional;

use Auxmoney\OpentracingBundle\Tests\Functional\JaegerWebFunctionalTest;
use Symfony\Component\Process\Process;

class FunctionalTest extends JaegerWebFunctionalTest
{
    /**
     * @dataProvider provideProjectSetups
     */
    public function testNestedSpansAndHeaderPropagation(string $projectSetup): void
    {
        $this->setUpTestProject($projectSetup);

        $p = new Process(['symfony', 'console', 'test:guzzle'], 'build/testproject');
        $p->mustRun();
        $output = $p->getOutput();
        $traceId = substr($output, 0, strpos($output, ':'));
        self::assertNotEmpty($traceId);

        $spans = $this->getSpansFromTrace($this->getTraceFromJaegerAPI($traceId));
        self::assertCount(5, $spans);

        $traceAsYAML = $this->getSpansAsYAML($spans, '[].{operationName: operationName, startTime: startTime, spanID: spanID, references: references, tags: tags[?key==\'http.status_code\' || key==\'command.exit-code\' || key==\'http.url\' || key==\'http.method\' || key==\'auxmoney-opentracing-bundle.span-origin\'].{key: key, value: value}}');
        self::assertStringEqualsFile(__DIR__ . '/FunctionalTest.expected.yaml', $traceAsYAML);
    }

    public function provideProjectSetups(): array
    {
        return [
            'no handler' => ['noHandler'],
            'existing handler stack' => ['existingHandlerStack'],
            'multiple existing handler stacks' => ['existingHandlerStacks'],
        ];
    }
}
