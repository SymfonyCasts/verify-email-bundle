<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyCasts\Bundle\VerifyEmail\Tests\Fixtures\AbstractVerifyEmailTestKernel;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class VerifyEmailBundleAutowireTest extends TestCase
{
    public function testVerifyEmailBundleInterfaceIsAutowiredByContainer(): void
    {
        $kernel = new VerifyEmailBundleIntegrationKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        $container->get(VerifyEmailHelperAutowireTest::class);

        $this->expectNotToPerformAssertions();
    }
}

class VerifyEmailBundleIntegrationKernel extends AbstractVerifyEmailTestKernel
{
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        parent::configureContainer($container, $loader);

        $container->autowire(VerifyEmailHelperAutowireTest::class)
            ->setPublic(true)
        ;
    }
}

class VerifyEmailHelperAutowireTest
{
    public function __construct(VerifyEmailHelperInterface $helper)
    {
    }
}
