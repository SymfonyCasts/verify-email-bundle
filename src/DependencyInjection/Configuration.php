<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfonycasts_verify_email');
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
