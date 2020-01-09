<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundleGuzzle\Tests\DependencyInjection;

use Auxmoney\OpentracingBundleGuzzle\DependencyInjection\OpentracingGuzzleExtension;
use Auxmoney\OpentracingBundleGuzzle\Middleware\GuzzleTracingHeaderInjection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpentracingExtensionTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        parent::setUp();

        $this->subject = new OpentracingGuzzleExtension();
    }

    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $this->subject->load([], $container);

        self::assertArrayHasKey(GuzzleTracingHeaderInjection::class, $container->getDefinitions());
    }
}
