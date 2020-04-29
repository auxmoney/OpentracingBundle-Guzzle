<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\DependencyInjection;

use Auxmoney\OpentracingGuzzleBundle\Decorator\GuzzleClientFactoryDecorator;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleRequestSpanning;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleTracingHeaderInjection;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class GuzzleHandlerStackCompilerPass implements CompilerPassInterface
{
    /**
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $clientServiceName => $definition) {
            if ($definition->getClass() === Client::class) {
                $this->addClientServiceDecorator($container, $clientServiceName);
            }
        }
    }

    private function addClientServiceDecorator(ContainerBuilder $container, string $clientServiceName): void
    {
        $decoratorDefinition = new Definition();
        $decoratorDefinition->setClass(Client::class);
        $decoratorDefinition->setDecoratedService($clientServiceName);
        $decoratorDefinition->setFactory([GuzzleClientFactoryDecorator::class, 'decorate']);

        $decoratorDefinition->setArguments([
            new Reference($clientServiceName . '.auxmoney_guzzle' . '.inner'),
            new Reference(GuzzleRequestSpanning::class),
            new Reference(GuzzleTracingHeaderInjection::class),
        ]);

        $container->setDefinition($clientServiceName . '.auxmoney_guzzle', $decoratorDefinition);
    }
}
