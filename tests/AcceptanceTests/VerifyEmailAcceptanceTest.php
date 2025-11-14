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
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\UriSigner as LegacyUriSigner;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use SymfonyCasts\Bundle\VerifyEmail\Tests\VerifyEmailTestKernel;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelper;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailAcceptanceTest extends TestCase
{
    /**
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testGenerateSignature(): void
    {
        $kernel = $this->getBootedKernel();
        $container = $kernel->getContainer();

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $container->get(VerifyEmailAcceptanceFixture::class);
        $helper = $testHelper->helper;

        $components = $helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');
        $expiresAt = $components->getExpiresAt()->getTimestamp();
        $actual = $components->getSignedUrl();
        $expected = $testHelper->uriSigner->sign(\sprintf(
            'http://localhost/verify/user?expires=%s&token=%s',
            $expiresAt,
            $testHelper->generator->createToken('1234', 'jr@rushlow.dev')
        ));

        self::assertSame($expected, $actual);
    }

    /** @group legacy */
    public function testValidateEmailSignature(): void
    {
        $kernel = $this->getBootedKernel();

        $container = $kernel->getContainer();

        /** @var VerifyEmailHelper $helper */
        $helper = $container->get(VerifyEmailAcceptanceFixture::class)->helper;
        $expires = new \DateTimeImmutable('+1 hour');

        $uriToTest = \sprintf(
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

        $test = \sprintf('%s&signature=%s', $uriToTest, urlencode($signature));

        $helper->validateEmailConfirmation($test, '1234', 'jr@rushlow.dev');
        $this->assertTrue(true, 'Test correctly does not throw an exception');
    }

    public function testValidateUsingRequestObject(): void
    {
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }
        $container = $this->getBootedKernel()->getContainer();

        /** @var VerifyEmailHelper $helper */
        $helper = $container->get(VerifyEmailAcceptanceFixture::class)->helper;
        $expires = new \DateTimeImmutable('+1 hour');

        $uriToTest = \sprintf(
            'http://localhost/verify/user?%s',
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

        $test = \sprintf('%s&signature=%s', $uriToTest, urlencode($signature));

        $helper->validateEmailConfirmationFromRequest(Request::create(uri: $test), '1234', 'jr@rushlow.dev');
        $this->assertTrue(true, 'Test correctly does not throw an exception');
    }

    public function testGenerateSignatureWithRelativePath(): void
    {
        $kernel = $this->getBootedKernel(['use_relative_path' => true]);
        $container = $kernel->getContainer();

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $container->get(VerifyEmailAcceptanceFixture::class);
        $helper = $testHelper->helper;

        $components = $helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');
        $expiresAt = $components->getExpiresAt()->getTimestamp();
        $actual = $components->getSignedUrl();
        $expected = $testHelper->uriSigner->sign(\sprintf(
            'verify/user?expires=%s&token=%s',
            $expiresAt,
            $testHelper->generator->createToken('1234', 'jr@rushlow.dev')
        ));

        self::assertSame($expected, $actual);
    }

    public function testValidateEmailSignatureWithRelativePath(): void
    {
        $kernel = $this->getBootedKernel(['use_relative_path' => true]);

        $container = $kernel->getContainer();

        /** @var VerifyEmailHelper $helper */
        $helper = $container->get(VerifyEmailAcceptanceFixture::class)->helper;
        $expires = new \DateTimeImmutable('+1 hour');

        $uriToTest = \sprintf(
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

        $test = \sprintf('%s&signature=%s', $uriToTest, urlencode($signature));

        $helper->validateEmailConfirmation($test, '1234', 'jr@rushlow.dev');
        $this->assertTrue(true, 'Test correctly does not throw an exception');
    }

    private function getBootedKernel(array $customConfig = []): KernelInterface
    {
        $builder = new ContainerBuilder();

        $builder->autowire(VerifyEmailAcceptanceFixture::class)
            ->setPublic(true)
            ->setArgument(1, new Reference('symfonycasts.verify_email.uri_signer'))
            ->setArgument(2, new Reference('symfonycasts.verify_email.token_generator'))
        ;

        $kernel = new VerifyEmailTestKernel(
            $builder,
            ['verify-test' => '/verify/user'],
            [],
            $customConfig
        );

        $kernel->boot();

        return $kernel;
    }
}

final class VerifyEmailAcceptanceFixture
{
    public function __construct(
        public VerifyEmailHelperInterface $helper,
        public LegacyUriSigner|UriSigner $uriSigner,
        public VerifyEmailTokenGenerator $generator,
    ) {
    }
}
