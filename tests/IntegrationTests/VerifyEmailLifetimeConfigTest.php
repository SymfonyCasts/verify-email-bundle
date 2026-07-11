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
use SymfonyCasts\Bundle\VerifyEmail\Tests\VerifyEmailTestKernel;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @author Pascal Cescon <pascal.cescon@gmail.com>
 */
final class VerifyEmailLifetimeConfigTest extends TestCase
{
    private const DEFAULT_LIFETIME = 3600;

    public function testDefaultLifetimeIsUsedByTheHelper(): void
    {
        $helper = $this->bootKernelAndGetHelper(new VerifyEmailTestKernel($this->createBuilder(), $this->routes()));

        $this->assertSignatureExpiresIn($helper, self::DEFAULT_LIFETIME);
    }

    public function testLifetimeConfiguredInUserlandIsUsedByTheHelper(): void
    {
        $helper = $this->bootKernelAndGetHelper(new VerifyEmailLifetimeTestKernel($this->createBuilder(), $this->routes()));

        $this->assertSignatureExpiresIn($helper, VerifyEmailLifetimeTestKernel::LIFETIME);
    }

    /**
     * The signature is generated from time(), so the expiration is bracketed between
     * the seconds observed right before and right after the call.
     */
    private function assertSignatureExpiresIn(VerifyEmailHelperInterface $helper, int $lifetime): void
    {
        $before = time();
        $components = $helper->generateSignature('verify_email', '1234', 'jr@rushlow.dev');
        $after = time();

        $expiresAt = $components->getExpiresAt()->getTimestamp();

        self::assertGreaterThanOrEqual($before + $lifetime, $expiresAt);
        self::assertLessThanOrEqual($after + $lifetime, $expiresAt);
    }

    private function routes(): array
    {
        return ['verify_email' => '/verify'];
    }

    private function createBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->autowire(VerifyEmailHelperHolder::class)
            ->setPublic(true)
        ;

        return $builder;
    }

    private function bootKernelAndGetHelper(VerifyEmailTestKernel $kernel): VerifyEmailHelperInterface
    {
        $kernel->boot();

        return $kernel->getContainer()->get(VerifyEmailHelperHolder::class)->helper;
    }
}

/**
 * Boots the test kernel with a "lifetime" explicitly configured, as an app would do.
 *
 * @internal
 */
final class VerifyEmailLifetimeTestKernel extends VerifyEmailTestKernel
{
    public const LIFETIME = 9999;

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(static function (ContainerBuilder $container) {
            $container->loadFromExtension('symfonycasts_verify_email', ['lifetime' => self::LIFETIME]);
        });
    }
}

/**
 * The helper is a private service - autowiring it into a public service is the
 * only way to get a hold of it from the container.
 *
 * @internal
 */
final class VerifyEmailHelperHolder
{
    public function __construct(public readonly VerifyEmailHelperInterface $helper)
    {
    }
}
