<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyCasts\Bundle\VerifyEmail\Tests\VerifyEmailTestKernel;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailBundleAutowireTest extends TestCase
{
    public function testVerifyEmailBundleInterfaceIsAutowiredByContainer(): void
    {
        $builder = new ContainerBuilder();
        $builder->autowire(VerifyEmailHelperAutowireTest::class)
            ->setPublic(true)
        ;

        $kernel = new VerifyEmailTestKernel($builder);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get(VerifyEmailHelperAutowireTest::class);

        $this->expectNotToPerformAssertions();
    }
}

class VerifyEmailHelperAutowireTest
{
    public function __construct(VerifyEmailHelperInterface $helper)
    {
    }
}
