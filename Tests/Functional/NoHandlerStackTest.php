<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\Functional;

use Auxmoney\OpentracingBundle\Tests\Functional\JaegerFunctionalTest;
use Symfony\Component\Process\Process;

class NoHandlerStackTest extends JaegerFunctionalTest
{
    public function testExceptionWithoutHandlerStack(): void
    {
        $this->copyTestProjectFiles('existingHandler');

        $this->composerDumpAutoload();

        $process = new Process(['symfony', 'console', 'cache:clear'], self::BUILD_TESTPROJECT);
        $returnCode = $process->run();

        self::assertSame(1, $returnCode);
        self::assertContains('The Guzzle client service named "', $process->getErrorOutput());
    }
}
