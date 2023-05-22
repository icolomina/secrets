<?php

namespace Ict\Secrets\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class IctSecretsConfiguration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tb = new TreeBuilder('ict_secrets');
        $tb
            ->getRootNode()
            ->children()
                ->scalarNode('hash_alg')
                    ->isRequired()
                    ->defaultValue('sha512')
                ->end()
                ->arrayNode('store')
                    ->children()
                         ->enumNode('type')
                             ->values(['redis'])
                             ->isRequired()
                         ->end()
                         ->variableNode('config')->end()
                    ->end()
                ->end()
                ->scalarNode('encoder')
                    ->isRequired()
                    ->defaultValue('sodium')
                ->end()
            ->end()
        ;

        return $tb;
    }
}
