<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingGuzzleBundle\DependencyInjection;

use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleRequestSpanning;
use Auxmoney\OpentracingGuzzleBundle\Middleware\GuzzleTracingHeaderInjection;
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
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $clientServiceName => $definition) {
            if ($definition->getClass() === Client::class) {
                $this->addMiddlewareToClient($container, $definition, $clientServiceName);
            } elseif ($definition->getClass() === HandlerStack::class) {
                $this->addUniqueMethodCallsToHandlerStack($definition);
            }
        }
    }

    private function addMiddlewareToClient(
        ContainerBuilder $container,
        Definition $definition,
        string $clientServiceName
    ): void {
        $configuration = $this->getConfiguration($definition);

        if (isset($configuration['handler'])) {
            $handlerServiceName = (string)$configuration['handler'];
            $this->addMiddlewareToExistingHandler(
                $definition,
                $container->getDefinition($handlerServiceName),
                $clientServiceName,
                $handlerServiceName
            );
            return;
        }

        $configuration['handler'] = new Reference('auxmoney_opentracing.guzzlehttp.default.handlerstack');
        $definition->setArguments([$configuration]);
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
            $this->addUniqueMethodCallsToHandlerStack($handlerDefinition);
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

    private function addUniqueMethodCallsToHandlerStack(Definition $handlerDefinition): void
    {
        $existingCalls = $handlerDefinition->getMethodCalls();
        foreach ($existingCalls as $existingCall) {
            if ($existingCall[0] === 'push') {
                $reference = $existingCall[1][0];
                if ($this->referencesAreAlreadyPushed($reference)) {
                    return;
                }
            }
        }

        $handlerDefinition->addMethodCall('push', [new Reference(GuzzleRequestSpanning::class)]);
        $handlerDefinition->addMethodCall('push', [new Reference(GuzzleTracingHeaderInjection::class)]);
    }

    /**
     * @param mixed $reference
     */
    private function referencesAreAlreadyPushed($reference): bool
    {
        return $reference instanceof Reference
            && in_array(
                $reference->__toString(),
                [GuzzleRequestSpanning::class, GuzzleTracingHeaderInjection::class],
                true
            );
    }
}
