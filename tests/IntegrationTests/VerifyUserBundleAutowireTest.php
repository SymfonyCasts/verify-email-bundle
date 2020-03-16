<?php

namespace SymfonyCasts\Bundle\VerifyUser\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Log\Logger;
use SymfonyCasts\Bundle\VerifyUser\SymfonyCastsVerifyUserBundle;
use SymfonyCasts\Bundle\VerifyUser\VerifyUserHelperInterface;

class VerifyBundleAutowireTest extends TestCase
{
    public function testVerifyBundleInterfaceIsAutowiredByContainer(): void
    {
        $kernel = new VerifyBundleIntegrationKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        $container->get(VerifyHelperAutowireTest::class);

        $this->expectNotToPerformAssertions();
    }
}

class VerifyBundleIntegrationKernel extends Kernel
{
    use MicroKernelTrait;

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
        return \sys_get_temp_dir().'/cache'.\spl_object_hash($this);
    }

    public function getLogDir()
    {
        return \sys_get_temp_dir().'/logs'.\spl_object_hash($this);
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

        $container->autowire(VerifyHelperAutowireTest::class)
            ->setPublic(true)
        ;

        // avoid logging request logs
        $container->register('logger', Logger::class)
            ->setArgument(0, LogLevel::EMERGENCY);
    }
}

class VerifyHelperAutowireTest
{
    public function __construct(VerifyUserHelperInterface $helper)
    {
    }
}
