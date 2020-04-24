<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyCasts\Bundle\VerifyUser\Tests\Fixtures\AbstractVerifyUserTestKernel;
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

class VerifyBundleIntegrationKernel extends AbstractVerifyUserTestKernel
{
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        parent::configureContainer($container, $loader);

        $container->autowire(VerifyHelperAutowireTest::class)
            ->setPublic(true)
        ;
    }
}

class VerifyHelperAutowireTest
{
    public function __construct(VerifyUserHelperInterface $helper)
    {
    }
}
