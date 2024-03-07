<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\UnitTests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\WrongEmailVerifyException;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelper;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @group time-sensitive
 */
final class VerifyEmailHelperTest extends TestCase
{
    private RouterInterface|MockObject $mockRouter;
    private UriSigner|MockObject $mockSigner;
    private VerifyEmailTokenGenerator|MockObject $tokenGenerator;

    protected function setUp(): void
    {
        ClockMock::register(VerifyEmailHelper::class);

        $this->mockRouter = $this->createMock(RouterInterface::class);
        $this->mockSigner = $this->createMock(UriSigner::class);
        $this->tokenGenerator = $this->createMock(VerifyEmailTokenGenerator::class);
    }

    public function testSignatureIsGenerated(): void
    {
        $expires = time() + 3600;

        $expectedSignedUrl = sprintf('/verify?expires=%s&signature=1234&token=hashedToken', $expires);

        $this->tokenGenerator
            ->expects($this->once())
            ->method('createToken')
            ->with('1234', 'jr@rushlow.dev')
            ->willReturn('hashedToken')
        ;

        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('app_verify_route', ['token' => 'hashedToken', 'expires' => $expires])
            ->willReturn(sprintf('/verify?expires=%s&token=hashedToken', $expires))
        ;

        $this->mockSigner
            ->expects($this->once())
            ->method('sign')
            ->with(sprintf('/verify?expires=%s&token=hashedToken', $expires))
            ->willReturn($expectedSignedUrl)
        ;

        $helper = $this->getHelper();
        $components = $helper->generateSignature('app_verify_route', '1234', 'jr@rushlow.dev');

        self::assertSame($expectedSignedUrl, $components->getSignedUrl());
    }

    public function testValidationThrowsEarlyOnInvalidSignature(): void
    {
        $this->markTestIncomplete('Refactor to use new method');
        $signedUrl = '/verify?expires=1&signature=1234%token=xyz';

        $this->mockSigner
            ->expects($this->once())
            ->method('check')
            ->with($signedUrl)
            ->willReturn(false)
        ;

        $this->mockQueryUtility
            ->expects($this->never())
            ->method('getExpiryTimestamp')
        ;

        $this->mockQueryUtility
            ->expects($this->never())
            ->method('getTokenFromQuery')
        ;

        $this->tokenGenerator
            ->expects($this->never())
            ->method('createToken')
        ;

        $helper = $this->getHelper();

        $this->expectException(InvalidSignatureException::class);

        $helper->validateEmailConfirmation($signedUrl, '1234', 'jr@rushlow.dev');
    }

    public function testExceptionThrownWithExpiredSignature(): void
    {
        $this->markTestIncomplete('Refactor to use new method');
        $timestamp = (new \DateTimeImmutable('-1 seconds'))->getTimestamp();
        $signedUrl = '/?expires='.$timestamp;

        $this->mockSigner
            ->expects($this->once())
            ->method('check')
            ->willReturn(true)
        ;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('getExpiryTimestamp')
            ->with($signedUrl)
            ->willReturn($timestamp)
        ;

        $this->expectException(ExpiredSignatureException::class);

        $helper = $this->getHelper();
        $helper->validateEmailConfirmation($signedUrl, '1234', 'jr@rushlow.dev');
    }

    public function testValidationThrowsWithInvalidToken(): void
    {
        $this->markTestIncomplete('Refactor to use new method');
        $signedUrl = '/verify?token=badToken';

        $this->mockSigner
            ->expects($this->once())
            ->method('check')
            ->with($signedUrl)
            ->willReturn(true)
        ;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('getExpiryTimestamp')
            ->with($signedUrl)
            ->willReturn(9999999999999999)
        ;

        $this->tokenGenerator
            ->expects($this->once())
            ->method('createToken')
            ->with('1234', 'jr@rushlow.dev')
            ->willReturn(base64_encode(hash_hmac('sha256', 'data', 'foo', true)))
        ;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('getTokenFromQuery')
            ->with($signedUrl)
            ->willReturn('badToken')
        ;

        $this->expectException(WrongEmailVerifyException::class);

        $helper = $this->getHelper();
        $helper->validateEmailConfirmation($signedUrl, '1234', 'jr@rushlow.dev');
    }

    private function getHelper(): VerifyEmailHelperInterface
    {
        return new VerifyEmailHelper($this->mockRouter, $this->mockSigner, $this->tokenGenerator, 3600);
    }
}
