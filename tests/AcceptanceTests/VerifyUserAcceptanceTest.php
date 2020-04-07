<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\AcceptanceTests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyCasts\Bundle\VerifyUser\Tests\Fixtures\AbstractVerifyUserTestKernel;
use SymfonyCasts\Bundle\VerifyUser\VerifyUserHelper;
use SymfonyCasts\Bundle\VerifyUser\VerifyUserHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 */
final class VerifyUserAcceptanceTest extends TestCase
{
    public function testGenerateSignature(): void
    {
        $kernel = new VerifyUserAcceptanceTestKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        /** @var VerifyUserHelper $helper */
        $helper = ($container->get(VerifyUserAcceptanceFixture::class))->helper;
        $components = $helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');

        $signature = $components->getSignature();
        $expiresAt = ($components->getExpiryTime())->getTimestamp();

        $queryParams = [
            'email' => 'jr@rushlow.dev',
            'expires' => $expiresAt,
            'id' => '1234',
        ];

        \ksort($queryParams);

        $hash = \base64_encode(\hash_hmac(
            'sha256',
            \sprintf('/verify/user?%s', \http_build_query($queryParams)),
            'foo',
            true
        ));

        $parsed = \parse_url($signature);
        \parse_str($parsed['query'], $result);

        self::assertTrue(\hash_equals($hash, $result['signature']));
        self::assertSame(
            \sprintf('/verify/user?expires=%s&signature=%s', $expiresAt, \urlencode($hash)),
            $signature
        );
    }
}

final class VerifyUserAcceptanceFixture
{
    public $helper;

    public function __construct(VerifyUserHelperInterface $helper)
    {
        $this->helper = $helper;
    }
}

final class VerifyUserAcceptanceTestKernel extends AbstractVerifyUserTestKernel
{
    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->add('verify-test', '/verify/user');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        parent::configureContainer($container, $loader);

        $container->autowire(VerifyUserAcceptanceFixture::class)
            ->setPublic(true)
        ;
    }
}
