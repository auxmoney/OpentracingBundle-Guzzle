<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\Tests\DependencyInjection;

use Auxmoney\OpentracingGuzzleBundle\DependencyInjection\OpentracingGuzzleExtension;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleTracingHeaderInjection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpentracingExtensionTest extends TestCase
{
    private OpentracingGuzzleExtension $subject;

    public function setUp(): void
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
