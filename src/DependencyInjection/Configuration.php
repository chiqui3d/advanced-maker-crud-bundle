<?php

namespace Charles\AdvancedMakerCrudBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('advanced_maker_crud');
        $rootNode
            ->children()
                ->scalarNode('base_template')->defaultValue('base.html.twig')->info('What is your base template?')->end()
            ->end()
        ;
        return $treeBuilder;
    }

}