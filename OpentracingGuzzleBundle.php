<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundleGuzzle;

use Auxmoney\OpentracingBundleGuzzle\DependencyInjection\GuzzleHandlerStackCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpentracingGuzzleBundle extends Bundle
{
    /**
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GuzzleHandlerStackCompilerPass());
    }
}
