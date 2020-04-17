<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\FunctionalTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyUser\Generator\VerifyUserTokenGenerator;
use SymfonyCasts\Bundle\VerifyUser\Tests\Fixtures\VerifyUserFixtureUser;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserQueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserUriSigningWrapper;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserUrlUtility;
use SymfonyCasts\Bundle\VerifyUser\VerifyUserHelper;
use SymfonyCasts\Bundle\VerifyUser\VerifyUserHelperInterface;

class VerifyUserHelperFunctionalTest extends TestCase
{
    private const FAKE_SIGNING_KEY = 'superSecret';
    private $mockRouter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testGenerateSignature(): void
    {
        $this->markTestSkipped('Unable to properly generate token without mocking DateTimeImmutable');

        $uri = '/verify';
        $user = new VerifyUserFixtureUser();

        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('app_verify_route')
            ->willReturn('/verify')
        ;

        $result = $this->getHelper()->generateSignature('app_verify_route', $user->id, $user->email);

        $parsedUri = parse_url($result->getSignature());
        parse_str($parsedUri['query'], $queryParams);

        $expectedQueryParams['email'] = $user->email;
        $expectedQueryParams['expires'] = $queryParams['expires'];
        $expectedQueryParams['id'] = $user->id;

        ksort($expectedQueryParams);
        $expectedQueryString = http_build_query($expectedQueryParams);

        $expectedUri = $uri.'?'.$expectedQueryString;
        $expectedHash = base64_encode(hash_hmac('sha256', $expectedUri, self::FAKE_SIGNING_KEY, true));

        self::assertTrue(hash_equals($expectedHash, $queryParams['signature']));
    }

    public function testValidSignature(): void
    {
        $uri = '/verify';
        $user = new VerifyUserFixtureUser();

        $testSignature = $this->getTestSignature(new \DateTimeImmutable('+1 hours'));

//        $queryParams['email'] = $user->email;
//        $queryParams['expires'] = (new \DateTimeImmutable('+1 hours'))->getTimestamp();
//        $queryParams['id'] = $user->id;
//
//        $queryString = http_build_query($queryParams);
//        $uriToSign = $uri.'?'.$queryString;
//
//        $signature = base64_encode(hash_hmac('sha256', $uriToSign, self::FAKE_SIGNING_KEY, true));
//        $queryParams['signature'] = $signature;
//
//        unset($queryParams['id'], $queryParams['email']);
//        ksort($queryParams);
//
//        $expectedSignedUri = $uri.'?'.http_build_query($queryParams);

        self::assertTrue($this->getHelper()->isValidSignature($testSignature, $user->id, $user->email));
    }

    private function getTestSignature(\DateTimeInterface $expires): string
    {
        $token = base64_encode(hash_hmac('sha256', json_encode(['1234', 'jr@rushlow.dev', $expires->getTimestamp()]), 'foo', true));

        $uri = sprintf('/verify?expires=%s&token=%s', $expires->getTimestamp(), $token);
        $signature = base64_encode(hash_hmac('sha256', $uri, 'foo', true));

        $uriComponents = parse_url($uri);
        parse_str($uriComponents['query'], $params);
        $params['signature'] = $signature;

        ksort($params);

        $sortedParams = http_build_query($params);

        $signedUri = sprintf('/verify?%s', $sortedParams);

        return $signedUri;
    }

    private function getHelper(): VerifyUserHelperInterface
    {
        return new VerifyUserHelper(
            $this->mockRouter,
            new VerifyUserUriSigningWrapper(self::FAKE_SIGNING_KEY),
            new VerifyUserQueryUtility(new VerifyUserUrlUtility()),
            new VerifyUserTokenGenerator('foo'),
            3600
        );
    }
}
