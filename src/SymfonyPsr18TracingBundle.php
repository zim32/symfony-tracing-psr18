<?php
declare(strict_types=1);

namespace Zim\SymfonyPsr18TracingBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zim\SymfonyPsr18TracingBundle\DependencyInjection\DecorateClientPass;

class SymfonyPsr18TracingBundle extends AbstractBundle
{
    protected string $extensionAlias = 'psr18_tracing';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('decorated_services')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('service')
                                ->isRequired()
                            ->end()
                        ->end()
                        ->children()
                            ->booleanNode('propagate')
                                ->defaultValue(false)
                                ->info('If true, trace data will be propagated when making requests')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter('tracing.psr18.decorated_services', $config['decorated_services']);
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DecorateClientPass());
    }
}
