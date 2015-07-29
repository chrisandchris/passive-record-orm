<?php

namespace ChrisAndChris\Common\RowMapperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('chris_and_chris_row_mapper');

        // @formatter:off
        $rootNode->children()
            ->arrayNode('types')
                ->prototype('array')
                    ->children()
                        ->arrayNode('params')
                            ->isRequired()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('required')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('snippets')
                ->prototype('array')
                    ->children()
                        ->scalarNode('code')
                            ->isRequired()->end()
                        ->arrayNode('routine')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
