<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\Functional;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
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
    private UrlGeneratorInterface&MockObject $mockRouter;
    private int $expiryTimestamp;

    protected function setUp(): void
    {
        ClockMock::register(VerifyEmailHelper::class);

        $this->expiryTimestamp = (time() + 3600);

        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testGenerateSignature(): void
    {
        $token = $this->getTestToken();

        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('app_verify_route', ['expires' => $this->expiryTimestamp, 'token' => $token])
            ->willReturn(\sprintf('/verify?expires=%s&token=%s', $this->expiryTimestamp, urlencode($token)))
        ;

        $result = $this->getHelper()->generateSignature('app_verify_route', '1234', 'jr@rushlow.dev');

        $parsedUri = parse_url($result->getSignedUrl());
        parse_str($parsedUri['query'], $queryParams); /** @phpstan-ignore-line offsetAccess.nonOffsetAccessible offset query always exists */
        $knownToken = $token;
        $testToken = $queryParams['token'];
        self::assertIsString($testToken);

        $knownSignature = $this->getTestSignature();
        $testSignature = $queryParams['signature'];
        self::assertIsString($testSignature);

        self::assertTrue(hash_equals($knownToken, $testToken));
        self::assertTrue(hash_equals($knownSignature, $testSignature));
    }

    private function getTestToken(): string
    {
        return base64_encode(hash_hmac(
            algo: 'sha256',
            data: json_encode(['1234', 'jr@rushlow.dev'], \JSON_THROW_ON_ERROR),
            key: 'foo',
            binary: true
        ));
    }

    private function getTestSignature(): string
    {
        $query = http_build_query(['expires' => $this->expiryTimestamp, 'token' => $this->getTestToken()], '', '&');
        $uri = \sprintf('/verify?%s', $query);

        return base64_encode(hash_hmac('sha256', $uri, 'foo', true));
    }

    private function getHelper(): VerifyEmailHelperInterface
    {
        return new VerifyEmailHelper(
            router: $this->mockRouter,
            uriSigner: new UriSigner('foo', 'signature'),
            tokenGenerator: new VerifyEmailTokenGenerator('foo'),
            lifetime: 3600
        );
    }
}
