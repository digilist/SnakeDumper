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

        $rootChildrenNode = $rootNode->children();

        // Property for dumper to use
        $rootChildrenNode->scalarNode('dumper')->cannotBeEmpty()->isRequired()->end();

        $rootChildrenNode
            ->arrayNode('output')
                ->children()
                    ->scalarNode('file')->defaultValue('php://stdout')->end()
                    ->booleanNode('gzip')->defaultFalse()->end()
                ->end()
            ->end()
        ;

        $rootChildrenNode
            ->arrayNode('database')
                ->children()
                    ->scalarNode('driver')->end()
                    ->scalarNode('host')->end()
                    ->scalarNode('user')->end()
                    ->scalarNode('password')->end()
                    ->scalarNode('dbname')->end()
                ->end()
            ->end()
        ;

        $tablePrototype = $rootChildrenNode
            ->arrayNode('tables')
                ->prototype('array')
                    ->children()
                        ->booleanNode('ignore_content')->defaultFalse()->end()
                        ->variableNode('columns')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
