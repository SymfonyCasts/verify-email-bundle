<?php

namespace SymfonyCasts\Bundle\VerifyEmail\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle;


class VerifyEmailTestKernel extends Kernel
{
    private $builder;
    private $routes;
    private $extraBundles;

    public function __construct(ContainerBuilder $builder = null, array $routes = [], array $bundles = [])
    {
        $this->builder = $builder;
        $this->routes = $routes;
        $this->extraBundles = $bundles;

        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return array_merge(
            $this->extraBundles,
            [
                new FrameworkBundle(),
                new SymfonyCastsVerifyEmailBundle(),
            ]
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if (null === $this->builder) {
            $this->builder = new ContainerBuilder();
        }

        $builder = $this->builder;

        $loader->load(function (ContainerBuilder $container) use ($builder, $loader) {
            $container->merge($builder);
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

    /**
     * @internal
     */
    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->routes as $name => $path) {
            $routes->add($name, new Route($path));
        }

        return $routes;
//        $file = (new \ReflectionObject($this))->getFileName();
//        /* @var RoutingPhpFileLoader $kernelLoader */
//        $kernelLoader = $loader->getResolver()->resolve($file);
//        $kernelLoader->setCurrentDir(\dirname($file));
//        $collection = new RouteCollection();
//
//
//        $this->configureRoutes(new RoutingConfigurator($collection, $kernelLoader, $file, $file));
//
//        foreach ($collection as $route) {
//            $controller = $route->getDefault('_controller');
//
//            if (\is_array($controller) && [0, 1] === array_keys($controller) && $this === $controller[0]) {
//                $route->setDefault('_controller', ['kernel', $controller[1]]);
//            }
//        }

//        return $collection;
    }
}
