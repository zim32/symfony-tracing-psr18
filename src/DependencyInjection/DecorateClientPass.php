<?php
declare(strict_types=1);

namespace Zim\SymfonyPsr18TracingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zim\SymfonyPsr18TracingBundle\Instrumentation\Psr18Client\InstrumentedPsr18Client;

class DecorateClientPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $decoratedServices = $container->getParameter('tracing.psr18.decorated_services');

        foreach ($decoratedServices as $idx => $params) {
            $decorated = (new Definition(InstrumentedPsr18Client::class))
                ->setDecoratedService($params['service'])
            ;

            $decorated->setArguments([
                '$inner' => new Reference('.inner'),
                '$httpTracer' => new Reference('tracing.scoped_tracer.http'),
                '$propagate' => $params['propagate'],
            ]);

            $container->setDefinition("tracing.instrumented_psr18_client.$idx", $decorated);
        }

        $container->getParameterBag()->remove('tracing.psr18.decorated_services');
    }
}
