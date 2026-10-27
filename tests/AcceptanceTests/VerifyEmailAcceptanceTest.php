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
use Symfony\Component\Routing\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
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

    /**
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testGenerateSignatureWithoutHost(): void
    {
        $kernel = $this->getBootedKernel(['sign_without_host' => true]);
        $container = $kernel->getContainer();

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $container->get(VerifyEmailAcceptanceFixture::class);

        $components = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');
        $expiresAt = $components->getExpiresAt()->getTimestamp();
        $actual = $components->getSignedUrl();
        $expected = 'http://localhost'.$testHelper->uriSigner->sign(\sprintf(
            '/verify/user?expires=%s&token=%s',
            $expiresAt,
            $testHelper->generator->createToken('1234', 'jr@rushlow.dev')
        ));

        self::assertSame($expected, $actual);
    }

    /**
     * @group legacy
     */
    public function testValidateEmailSignatureFromAbsoluteUrlWithoutHost(): void
    {
        $kernel = $this->getBootedKernel(['sign_without_host' => true]);

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $kernel->getContainer()->get(VerifyEmailAcceptanceFixture::class);
        $signedUrl = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();

        // The deprecated method is documented to take the URL the user clicked, which may carry any host.
        $signedHost = 'http://localhost';
        self::assertStringContainsString($signedHost, $signedUrl);

        $onAnotherHost = str_replace($signedHost, 'https://example.com', $signedUrl);

        $testHelper->helper->validateEmailConfirmation($onAnotherHost, '1234', 'jr@rushlow.dev');
    }

    public function testValidateEmailSignatureFromRequestWithoutHost(): void
    {
        /** @legacy - Remove conditional in 2.0 */
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }

        $kernel = $this->getBootedKernel(['sign_without_host' => true]);

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $kernel->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $signedUrl = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();

        self::assertTrue($this->canBeValidatedBy($testHelper->helper, Request::create($signedUrl)));
    }

    public function testValidateEmailSignatureFromRequestWithoutHostOnAnotherHost(): void
    {
        /** @legacy - Remove conditional in 2.0 */
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }

        $kernel = $this->getBootedKernel(['sign_without_host' => true]);

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $kernel->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $signedUrl = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();
        $signedHost = 'http://localhost';

        self::assertStringContainsString($signedHost, $signedUrl);

        $onAnotherHost = str_replace($signedHost, 'https://other.example', $signedUrl);

        self::assertTrue($this->canBeValidatedBy($testHelper->helper, Request::create($onAnotherHost)));
    }

    /**
     * The route forces https while the context is on http, so the generator returns an absolute
     * URL instead of a path.
     *
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testGenerateSignatureWithoutHostRejectsRouteWithSchemes(): void
    {
        $kernel = $this->getBootedKernel(
            customConfig: ['sign_without_host' => true],
            routes: ['verify-test' => new Route('/verify/user', schemes: ['https'])]
        );

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $kernel->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The route "verify-test" generates a URL with the host "localhost" instead of a path');

        $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');
    }

    /**
     * An edge case, and the reason the exception message mentions `framework.router.default_uri`:
     * the generator only writes the host into the URL when it differs from the context, so a route
     * pinned to that same host generates a plain path and passes.
     *
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testGenerateSignatureWithoutHostAcceptsRouteBoundToTheContextHost(): void
    {
        $kernel = $this->getBootedKernel(
            customConfig: ['sign_without_host' => true],
            routes: ['verify-test' => new Route('/verify/user', host: 'localhost')]
        );

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $kernel->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $components = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');
        // despite the host on the route, only the path is signed
        $expected = 'http://localhost'.$testHelper->uriSigner->sign(\sprintf(
            '/verify/user?expires=%s&token=%s',
            $components->getExpiresAt()->getTimestamp(),
            $testHelper->generator->createToken('1234', 'jr@rushlow.dev')
        ));

        self::assertSame($expected, $components->getSignedUrl());
    }

    /**
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testGenerateSignatureWithoutHostRejectsRouteBoundToAHost(): void
    {
        $kernel = $this->getBootedKernel(
            customConfig: ['sign_without_host' => true],
            routes: ['verify-test' => new Route('/verify/user', host: 'mail.example.com')]
        );

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $kernel->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The route "verify-test" generates a URL with the host "mail.example.com" instead of a path');

        $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev');
    }

    /**
     * MakerBundle puts this return value straight into the email, so it has to be a link that
     * works on its own. The host comes from the router context.
     *
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testGenerateSignatureWithoutHostReturnsAnAbsoluteUrl(): void
    {
        $kernel = $this->getBootedKernel(['sign_without_host' => true]);

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $kernel->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $signedUrl = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();

        self::assertStringStartsWith('http://localhost/verify/user?', $signedUrl);
    }

    public function tamperedConfirmationUrlProviderForSigningWithHost(): \Generator
    {
        yield 'another host' => ['http://localhost', 'https://other.example'];
        yield 'another path' => ['/verify/user', '/verify/admin'];
        yield 'an extra query parameter' => ['&token=', '&role=admin&token='];
        yield 'a later expiry' => ['expires=', 'expires=9'];
    }

    /**
     * @dataProvider tamperedConfirmationUrlProviderForSigningWithHost
     */
    public function testValidateEmailSignatureFromRequestWithHostRejectsATamperedUrl(string $search, string $replace): void
    {
        /** @legacy - Remove conditional in 2.0 */
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $this->getBootedKernel()->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $signedUrl = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();
        self::assertStringContainsString($search, $signedUrl);

        $tamperedUrl = str_replace($search, $replace, $signedUrl);

        $this->expectException(InvalidSignatureException::class);

        $testHelper->helper->validateEmailConfirmationFromRequest(Request::create($tamperedUrl), '1234', 'jr@rushlow.dev');
    }

    public function tamperedConfirmationUrlProviderForSigningWithoutHost(): \Generator
    {
        yield 'another path' => ['/verify/user', '/verify/admin'];
        yield 'an extra query parameter' => ['&token=', '&role=admin&token='];
        yield 'a later expiry' => ['expires=', 'expires=9'];
    }

    /**
     * @dataProvider tamperedConfirmationUrlProviderForSigningWithoutHost
     */
    public function testValidateEmailSignatureFromRequestWithoutHostRejectsATamperedUrl(string $search, string $replace): void
    {
        /** @legacy - Remove conditional in 2.0 */
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }

        /** @var VerifyEmailAcceptanceFixture $testHelper */
        $testHelper = $this->getBootedKernel(['sign_without_host' => true])->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $signedUrl = $testHelper->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();
        self::assertStringContainsString($search, $signedUrl);

        $tamperedUrl = str_replace($search, $replace, $signedUrl);

        $this->expectException(InvalidSignatureException::class);

        $testHelper->helper->validateEmailConfirmationFromRequest(Request::create($tamperedUrl), '1234', 'jr@rushlow.dev');
    }

    public function testEnablingSignWithoutHostOptionInvalidatesExistingConfirmationLinks(): void
    {
        /** @legacy - Remove conditional in 2.0 */
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }

        /** @var VerifyEmailAcceptanceFixture $testHelperBeforeTheSwitch */
        $testHelperBeforeTheSwitch = $this->getBootedKernel()->getContainer()->get(VerifyEmailAcceptanceFixture::class);
        $signedUrl = $testHelperBeforeTheSwitch->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();

        /** @var VerifyEmailAcceptanceFixture $testHelperAfterTheSwitch */
        $testHelperAfterTheSwitch = $this->getBootedKernel(['sign_without_host' => true])->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $this->expectException(InvalidSignatureException::class);

        $testHelperAfterTheSwitch->helper->validateEmailConfirmationFromRequest(Request::create($signedUrl), '1234', 'jr@rushlow.dev');
    }

    public function testDisablingSignWithoutHostOptionInvalidatesExistingConfirmationLinks(): void
    {
        /** @legacy - Remove conditional in 2.0 */
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }

        /** @var VerifyEmailAcceptanceFixture $testHelperBeforeTheSwitch */
        $testHelperBeforeTheSwitch = $this->getBootedKernel(['sign_without_host' => true])->getContainer()->get(VerifyEmailAcceptanceFixture::class);
        $signedUrl = $testHelperBeforeTheSwitch->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl();

        /** @var VerifyEmailAcceptanceFixture $testHelperAfterTheSwitch */
        $testHelperAfterTheSwitch = $this->getBootedKernel()->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        $this->expectException(InvalidSignatureException::class);

        $testHelperAfterTheSwitch->helper->validateEmailConfirmationFromRequest(Request::create($signedUrl), '1234', 'jr@rushlow.dev');
    }

    public function testSwitchingTheSignWithoutHostOptionBackMakesExistingConfirmationLinksValidAgain(): void
    {
        /** @legacy - Remove conditional in 2.0 */
        if (!class_exists(UriSigner::class)) {
            $this->markTestSkipped('Requires symfony/http-foundation 6.4+');
        }

        /** @var VerifyEmailAcceptanceFixture $testHelperBeforeTheSwitch */
        $testHelperBeforeTheSwitch = $this->getBootedKernel()->getContainer()->get(VerifyEmailAcceptanceFixture::class);
        $request = Request::create($testHelperBeforeTheSwitch->helper->generateSignature('verify-test', '1234', 'jr@rushlow.dev')->getSignedUrl());

        /** @var VerifyEmailAcceptanceFixture $testHelperAfterTheSwitch */
        $testHelperAfterTheSwitch = $this->getBootedKernel(['sign_without_host' => true])->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        /** @var VerifyEmailAcceptanceFixture $testHelperAfterSwitchingBack */
        $testHelperAfterSwitchingBack = $this->getBootedKernel()->getContainer()->get(VerifyEmailAcceptanceFixture::class);

        self::assertFalse($this->canBeValidatedBy($testHelperAfterTheSwitch->helper, $request));
        self::assertTrue($this->canBeValidatedBy($testHelperAfterSwitchingBack->helper, $request));
    }

    private function canBeValidatedBy(VerifyEmailHelperInterface $helper, Request $request): bool
    {
        try {
            $helper->validateEmailConfirmationFromRequest($request, '1234', 'jr@rushlow.dev');

            return true;
        } catch (InvalidSignatureException $e) {
            return false;
        }
    }

    /**
     * @param array<string, mixed>        $customConfig
     * @param array<string, string|Route> $routes
     */
    private function getBootedKernel(array $customConfig = [], array $routes = ['verify-test' => '/verify/user']): KernelInterface
    {
        $builder = new ContainerBuilder();

        $builder->autowire(VerifyEmailAcceptanceFixture::class)
            ->setPublic(true)
            ->setArgument(1, new Reference('symfonycasts.verify_email.uri_signer'))
            ->setArgument(2, new Reference('symfonycasts.verify_email.token_generator'))
        ;

        $kernel = new VerifyEmailTestKernel(
            $builder,
            $routes,
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
