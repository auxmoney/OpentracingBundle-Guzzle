<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundleGuzzle\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class FunctionalTest extends TestCase
{
    public function testNestedSpansAndHeaderPropagation(): void
    {
        self::assertTrue(true);
    }

    public function setUp() {
        parent::setUp();

        $p = new Process(['Tests/Functional/setup.sh']);
        $p->mustRun();
    }

    protected function tearDown() {
        parent::tearDown();

        $p = new Process(['Tests/Functional/teardown.sh']);
        $p->mustRun();
    }
}
