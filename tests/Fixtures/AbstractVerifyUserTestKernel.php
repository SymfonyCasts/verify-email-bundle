<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\FrameworkBundle\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use SymfonyCasts\Bundle\VerifyUser\SymfonyCastsVerifyUserBundle;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 */
class AbstractVerifyUserTestKernel extends Kernel
{
    use MicroKernelTrait;

    private $cacheDir;

    private $logDir;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SymfonyCastsVerifyUserBundle(),
        ];
    }

    public function getCacheDir()
    {
        if (null === $this->cacheDir) {
            return \sys_get_temp_dir().'/cache'.\spl_object_hash($this);
        }

        return $this->cacheDir;
    }

    public function getLogDir()
    {
        if (null === $this->logDir) {
            return \sys_get_temp_dir().'/logs'.\spl_object_hash($this);
        }

        return $this->logDir;
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->loadFromExtension('framework', [
            'secret' => 'foo',
            'router' => [
                'utf8' => true,
            ],
        ]);
    }
}
