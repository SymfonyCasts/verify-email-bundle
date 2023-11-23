<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class SymfonyCastsVerifyEmailExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('verify_email_services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        if (!$configuration) {
            throw new \Exception('Configuration is not expected to be null');
        }

        $config = $this->processConfiguration($configuration, $configs);

        $helperDefinition = $container->getDefinition('symfonycasts.verify_email.helper');
        $helperDefinition->replaceArgument(4, $config['lifetime']);
    }

    public function getAlias(): string
    {
        return 'symfonycasts_verify_email';
    }
}
