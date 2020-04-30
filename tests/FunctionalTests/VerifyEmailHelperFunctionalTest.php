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
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use SymfonyCasts\Bundle\VerifyEmail\Tests\Fixtures\VerifyEmailFixtureUser;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailQueryUtility;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailUrlUtility;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelper;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @group time-sensitive
 */
class VerifyEmailHelperFunctionalTest extends TestCase
{
    private $mockRouter;
    private $expiryTimeStamp;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        ClockMock::register(VerifyEmailHelper::class);

        $this->expiryTimeStamp = (time() + 3600);

        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testGenerateSignature(): void
    {
        $user = new VerifyEmailFixtureUser();

        $token = $this->getTestToken();

        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('app_verify_route', ['expires' => $this->expiryTimeStamp, 'token' => $token])
            ->willReturn(sprintf('/verify?expires=%s&token=%s', $this->expiryTimeStamp, urlencode($token)))
        ;

        $result = $this->getHelper()->generateSignature('app_verify_route', $user->id, $user->email);

        $parsedUri = parse_url($result->getSignedUrl());
        parse_str($parsedUri['query'], $queryParams);

        $knownToken = $token;
        $testToken = $queryParams['token'];

        $knownSignature = $this->getTestSignature();
        $testSignature = $queryParams['signature'];

        self::assertTrue(hash_equals($knownToken, $testToken));
        self::assertTrue(hash_equals($knownSignature, $testSignature));
    }

    public function testValidSignature(): void
    {
        $user = new VerifyEmailFixtureUser();

        $testSignature = $this->getTestSignedUri();

        self::assertTrue($this->getHelper()->isValidSignature($testSignature, $user->id, $user->email));
    }

    private function getTestToken(): string
    {
        return base64_encode(hash_hmac('sha256', json_encode(['1234', 'jr@rushlow.dev', $this->expiryTimeStamp]), 'foo', true));
    }

    private function getTestSignature(): string
    {
        $query = http_build_query(['expires' => $this->expiryTimeStamp, 'token' => $this->getTestToken()], '', '&');
        $uri = sprintf('/verify?%s', $query);

        return base64_encode(hash_hmac('sha256', $uri, 'foo', true));
    }

    private function getTestSignedUri(): string
    {
        $token = urlencode($this->getTestToken());

        $uri = sprintf('/verify?expires=%s&token=%s', $this->expiryTimeStamp, $token);
        $signature = base64_encode(hash_hmac('sha256', $uri, 'foo', true));

        $uriComponents = parse_url($uri);
        parse_str($uriComponents['query'], $params);
        $params['signature'] = $signature;

        ksort($params);

        $sortedParams = http_build_query($params);

        return sprintf('/verify?%s', $sortedParams);
    }

    private function getHelper(): VerifyEmailHelperInterface
    {
        return new VerifyEmailHelper(
            $this->mockRouter,
            new UriSigner('foo', 'signature'),
            new VerifyEmailQueryUtility(new VerifyEmailUrlUtility()),
            new VerifyEmailTokenGenerator('foo'),
            3600
        );
    }
}
