<?php

declare(strict_types=1);

namespace Umanit\TwigImage\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('umanit_twig_image');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('lazy_load')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class_selector')
                            ->defaultValue('lazy')
                        ->end() // class_selector
                        ->scalarNode('placeholder_class_selector')
                            ->defaultValue('lazy-placeholder')
                        ->end() // placeholder_class_selector
                        ->scalarNode('blur_class_selector')
                            ->defaultValue('lazy-blur')
                        ->end() // blur_class_selector
                    ->end()
                ->end() // lazy_load
            ->end()
        ;

        return $treeBuilder;
    }
}
