<?php

namespace SymfonyCasts\Bundle\VerifyUser\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('symfonycasts_verify_user');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('lifetime')
                    ->defaultValue(3600)
                    ->info('The length of time in seconds that a signed URI is valid for after it is created.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
