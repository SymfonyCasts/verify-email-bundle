<?php

namespace SymfonyCasts\Bundle\VerifyEmail\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle;


class VerifyEmailTestKernel extends Kernel
{
    public function __construct(string $environment = 'test', bool $debug = true)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SymfonyCastsVerifyEmailBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $container->loadFromExtension(
                'framework',
                [
                    'secret' => 'foo',
                    'router' => [
                        'resource' => 'kernel::loadRoutes',
                        'type' => 'service',
                        'utf8' => true,
                    ],
                ]
            );

            $container->addObjectResource($this);
            $container->register('kernel', static::class)
                ->addTag('controller.service_arguments')
                ->setAutoconfigured(true)
                ->setSynthetic(true)
                ->setPublic(true)
            ;

            $kernelDefinition = $container->getDefinition('kernel');
            $kernelDefinition->addTag('routing.route_loader');
        });
    }

    public function registerRoutes(array $routes): void
    {

    }
}
