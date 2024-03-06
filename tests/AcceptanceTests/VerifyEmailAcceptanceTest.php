<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\AcceptanceTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;
use SymfonyCasts\Bundle\VerifyEmail\Tests\VerifyEmailTestKernel;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelper;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailAcceptanceTest extends TestCase
{
    public function testGenerateSignature(): void
    {
        $kernel = $this->getBootedKernel();

        $container = $kernel->getContainer();

        /** @var VerifyEmailHelper $helper */
        $helper = $container->get(VerifyEmailAcceptanceFixture::class)->helper;

        $components = $helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');

        $signature = $components->getSignedUrl();
        $expiresAt = $components->getExpiresAt()->getTimestamp();

        $expectedUserData = json_encode(['1234', 'jr@rushlow.dev']);

        $expectedToken = base64_encode(hash_hmac('sha256', $expectedUserData, 'foo', true));

        $expectedSignature = base64_encode(hash_hmac(
            'sha256',
            sprintf('http://localhost/verify/user?expires=%s&token=%s', $expiresAt, urlencode($expectedToken)),
            'foo',
            true
        ));

        $parsed = parse_url($signature);
        parse_str($parsed['query'], $result);

        self::assertTrue(hash_equals($expectedSignature, $result['signature']));
        self::assertSame(
            sprintf('http://localhost/verify/user?expires=%s&signature=%s&token=%s', $expiresAt, urlencode($expectedSignature), urlencode($expectedToken)),
            $signature
        );
    }

    public function testValidateEmailSignature(): void
    {
        $this->markTestIncomplete('Removed');
        $kernel = $this->getBootedKernel();

        $container = $kernel->getContainer();

        /** @var VerifyEmailHelper $helper */
        $helper = $container->get(VerifyEmailAcceptanceFixture::class)->helper;
        $expires = new \DateTimeImmutable('+1 hour');

        $uriToTest = sprintf(
            '/verify/user?%s',
            http_build_query([
                'expires' => $expires->getTimestamp(),
                'token' => base64_encode(hash_hmac(
                    'sha256',
                    json_encode(['1234', 'jr@rushlow.dev']),
                    'foo',
                    true
                )),
            ])
        );

        $signature = base64_encode(hash_hmac('sha256', $uriToTest, 'foo', true));

        $test = sprintf('%s&signature=%s', $uriToTest, urlencode($signature));

        $helper->validateEmailConfirmation($test, '1234', 'jr@rushlow.dev');
        $this->assertTrue(true, 'Test correctly does not throw an exception');
    }

    private function getBootedKernel(): KernelInterface
    {
        $builder = new ContainerBuilder();
        $builder->autowire(VerifyEmailAcceptanceFixture::class)
            ->setPublic(true)
        ;

        $kernel = new VerifyEmailTestKernel(
            $builder,
            ['verify-test' => '/verify/user']
        );

        $kernel->boot();

        return $kernel;
    }
}

final class VerifyEmailAcceptanceFixture
{
    public $helper;

    public function __construct(VerifyEmailHelperInterface $helper)
    {
        $this->helper = $helper;
    }
}
