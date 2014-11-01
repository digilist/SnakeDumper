<?php

namespace Digilist\SnakeDumper\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SnakeConfigurationTree implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('anonymizer');

        $rootNode
            ->children()
                ->arrayNode('database')
                    ->children()
                        ->scalarNode('driver')->end()
                        ->scalarNode('host')->end()
                        ->scalarNode('user')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('dbname')->end()
                    ->end()
                ->end()
                ->arrayNode('settings')
                    ->children()
                        ->scalarNode('output')->end()
                        ->scalarNode('file')->end()
                    ->end()
                ->end()
                ->arrayNode('anonymize')
                    ->children()
                        ->scalarNode('format')->end()
//                        ->arrayNode()
        ;

        return $treeBuilder;
    }
}
