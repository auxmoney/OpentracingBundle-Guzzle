<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundleGuzzle\DependencyInjection;

use Auxmoney\OpentracingBundleGuzzle\Middleware\GuzzleTracingHeaderInjection;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

final class GuzzleHandlerStackCompilerPass implements CompilerPassInterface
{
    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $clientServiceName => $definition) {
            if ($definition->getClass() === Client::class) {
                $configuration = $this->getConfiguration($definition);

                if (isset($configuration['handler'])) {
                    $handlerServiceName = (string) $configuration['handler'];
                    $this->addMiddlewareToExistingHandler(
                        $definition,
                        $container->getDefinition($handlerServiceName),
                        $clientServiceName,
                        $handlerServiceName
                    );
                    continue;
                }

                $configuration['handler'] = new Reference('auxmoney_opentracing.guzzlehttp.default.handlerstack');
                $definition->setArguments([$configuration]);
            }
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function getConfiguration(Definition $definition): array
    {
        $arguments = $definition->getArguments();

        $configuration = [];
        if (isset($arguments[0])) {
            $configuration = $arguments[0];
        } elseif (isset($arguments['$config'])) {
            $configuration = $arguments['$config'];
        }

        return $configuration;
    }

    private function addMiddlewareToExistingHandler(
        Definition $clientDefinition,
        Definition $handlerDefinition,
        string $clientServiceName,
        string $handlerServiceName
    ): void {
        if ($handlerDefinition->getClass() === HandlerStack::class) {
            $handlerDefinition->addMethodCall('push', [new Reference(GuzzleTracingHeaderInjection::class)]);
            $clientDefinition->addTag('auxmoney_opentracing.enabled');
            return;
        }

        throw new RuntimeException(
            sprintf(
                'The Guzzle client service named "%s" has a configured handler "%s", which is not a %s,'
                    . ' you cannot use the guzzle plugin until you refactored this!',
                $clientServiceName,
                $handlerServiceName,
                HandlerStack::class
            )
        );
    }
}
