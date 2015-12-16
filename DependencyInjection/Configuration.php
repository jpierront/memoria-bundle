<?php

namespace CuteNinja\MemoriaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('cute_ninja_memoria');

        $rootNode
            ->children()
                ->scalarNode('console_path')
                    ->isRequired(true)
                ->end()
                ->arrayNode('additional_entity_managers')
                    ->prototype('scalar')->defaultNull()->end()
                ->end()
                ->arrayNode('fixtures')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('resource')
                                ->isRequired(true)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
