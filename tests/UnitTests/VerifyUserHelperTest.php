<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\UnitTests;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\VerifyUser\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyUser\Generator\VerifyUserTokenGenerator;
use SymfonyCasts\Bundle\VerifyUser\Tests\Fixtures\VerifyUserFixtureUser;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserQueryUtility;
use SymfonyCasts\Bundle\VerifyUser\VerifyUserHelper;
use SymfonyCasts\Bundle\VerifyUser\VerifyUserHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @group time-sensitive
 */
final class VerifyUserHelperTest extends TestCase
{
    private $mockRouter;
    private $mockSigner;
    private $mockQueryUtility;
    private $tokenGenerator;

    protected function setUp(): void
    {
        ClockMock::register(VerifyUserHelper::class);

        $this->mockRouter = $this->createMock(RouterInterface::class);
        $this->mockSigner = $this->createMock(UriSigner::class);
        $this->mockQueryUtility = $this->createMock(VerifyUserQueryUtility::class);
        $this->tokenGenerator = $this->createMock(VerifyUserTokenGenerator::class);
    }

    public function testSignatureIsGenerated(): void
    {
        $expires = time() + 3600;

        $expectedSignature = '/verify?signature=1234';

        $this->tokenGenerator
            ->expects($this->once())
            ->method('createToken')
            ->with('1234', 'jr@rushlow.dev', $expires)
            ->willReturn('hashedToken')
        ;

        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('app_verify_route', ['token' => 'hashedToken', 'expires' => $expires])
            ->willReturn('/verify')
        ;

        $this->mockSigner
            ->expects($this->once())
            ->method('sign')
            ->with('/verify')
            ->willReturn($expectedSignature)
        ;

        $helper = $this->getHelper();
        $components = $helper->generateSignature('app_verify_route', '1234', 'jr@rushlow.dev');

        self::assertSame($expectedSignature, $components->getSignature());
    }

    public function testIsValidSignature(): void
    {
        $user = new VerifyUserFixtureUser();
        $expires = time() + 3600;
        $signature = '/verify?signature=1234';

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('getExpiryTimeStamp')
            ->with($signature)
            ->willReturn($expires)
        ;

        $this->tokenGenerator
            ->expects($this->once())
            ->method('createToken')
            ->with($user->id, $user->email, $expires)
            ->willReturn('someToken')
        ;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('getTokenFromQuery')
            ->with($signature)
            ->willReturn('someToken')
        ;

        $this->mockSigner
            ->expects($this->once())
            ->method('check')
            ->with($signature)
            ->willReturn(false)
        ;

        $helper = $this->getHelper();
        $helper->isValidSignature($signature, $user->id, $user->email);
    }

    public function testExceptionThrownWithExpiredSignature(): void
    {
        $timestamp = (new \DateTimeImmutable('-1 seconds'))->getTimestamp();
        $signature = '/?expires='.$timestamp;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('getExpiryTimeStamp')
            ->with($signature)
            ->willReturn($timestamp)
        ;

        $this->expectException(ExpiredSignatureException::class);
        $helper = $this->getHelper();
        $helper->isValidSignature($signature, '1234', 'jr@rushlow.dev');
    }

    public function testGetLifetimeReturnsIntFromLifetimeProperty(): void
    {
        $helper = $this->getHelper();
        self::assertSame(3600, $helper->getSignatureLifetime());
    }

    private function getHelper(): VerifyUserHelperInterface
    {
        return new VerifyUserHelper($this->mockRouter, $this->mockSigner, $this->mockQueryUtility, $this->tokenGenerator, 3600);
    }
}
