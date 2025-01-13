<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\FunctionalTests;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\UriSigner as LegacyUriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailQueryUtility;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelper;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @group time-sensitive
 */
final class VerifyEmailHelperFunctionalTest extends TestCase
{
    private $mockRouter;
    private UriSigner|LegacyUriSigner $uriSigner;
    private $expiryTimestamp;

    protected function setUp(): void
    {
        ClockMock::register(VerifyEmailHelper::class);

        $this->expiryTimestamp = (time() + 3600);

        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
    }

    /**
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testGenerateSignature(): void
    {
        $token = $this->getTestToken();

        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('app_verify_route', ['expires' => $this->expiryTimestamp, 'token' => $token])
            ->willReturn(\sprintf('/verify?expires=%s&token=%s', $this->expiryTimestamp, urlencode($token)))
        ;

        $actual = $this->getHelper()->generateSignature('app_verify_route', '1234', 'jr@rushlow.dev')->getSignedUrl();
        $expected = $this->uriSigner->sign(\sprintf('/verify?expires=%s&token=%s', $this->expiryTimestamp, urlencode($token)));

        self::assertSame($expected, $actual);
    }

    /**
     * @legacy - Remove annotation in 2.0
     *
     * @group legacy
     */
    public function testValidSignature(): void
    {
        $testSignature = $this->getTestSignedUri();

        $this->getHelper()->validateEmailConfirmation($testSignature, '1234', 'jr@rushlow.dev');
        $this->assertTrue(true, 'Test correctly does not throw an exception');
    }

    private function getTestToken(): string
    {
        return base64_encode(hash_hmac('sha256', json_encode(['1234', 'jr@rushlow.dev']), 'foo', true));
    }

    private function getTestSignature(): string
    {
        $query = http_build_query(['expires' => $this->expiryTimestamp, 'token' => $this->getTestToken()], '', '&');
        $uri = \sprintf('/verify?%s', $query);

        return base64_encode(hash_hmac('sha256', $uri, 'foo', true));
    }

    private function getTestSignedUri(): string
    {
        $token = urlencode($this->getTestToken());

        $uri = \sprintf('/verify?expires=%s&token=%s', $this->expiryTimestamp, $token);
        $signature = base64_encode(hash_hmac('sha256', $uri, 'foo', true));

        $uriComponents = parse_url($uri);
        parse_str($uriComponents['query'], $params);
        $params['signature'] = $signature;

        ksort($params);

        $sortedParams = http_build_query($params);

        return \sprintf('/verify?%s', $sortedParams);
    }

    private function getHelper(): VerifyEmailHelperInterface
    {
        if (class_exists(UriSigner::class)) {
            $this->uriSigner = new UriSigner('foo', 'signature');
        } else {
            $this->uriSigner = new LegacyUriSigner('foo', 'signature');
        }

        return new VerifyEmailHelper(
            $this->mockRouter,
            $this->uriSigner,
            new VerifyEmailQueryUtility(),
            new VerifyEmailTokenGenerator('foo'),
            3600
        );
    }
}
