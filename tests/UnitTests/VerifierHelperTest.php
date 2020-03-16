<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\UnitTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\VerifyUser\Collection\QueryParamCollection;
use SymfonyCasts\Bundle\VerifyUser\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyUser\Util\QueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\UriSigningWrapper;
use SymfonyCasts\Bundle\VerifyUser\VerifyHelper;
use SymfonyCasts\Bundle\VerifyUser\VerifyHelperInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifierHelperTest extends TestCase
{
    private $mockRouter;
    private $mockSigner;
    private $mockQueryUtility;

    protected function setUp(): void
    {
        $this->mockRouter = $this->createMock(RouterInterface::class);
        $this->mockSigner = $this->createMock(UriSigningWrapper::class);
        $this->mockQueryUtility = $this->createMock(QueryUtility::class);
    }

    public function testSignatureIsGenerated(): void
    {
        $uriToBeSigned = '/verify?id=1234&email=jr@rushlow.dev&expires=';
        $signature = '?signature=abc';
        $signedUri = $uriToBeSigned.$signature;

        $this->mockRouter
            ->expects($this->once())
            ->method('generate')
            ->with('app_verify_route', [])
            ->willReturn('/verify')
        ;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('addQueryParams')
            ->with(self::isInstanceOf(QueryParamCollection::class), '/verify')
            ->willReturn($uriToBeSigned)
        ;

        $this->mockSigner
            ->expects($this->once())
            ->method('signUri')
            ->with($uriToBeSigned)
            ->willReturn($signedUri)
        ;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('removeQueryParam')
            ->with(self::isInstanceOf(QueryParamCollection::class), $signedUri)
            ->willReturn($signature)
        ;

        $helper = $this->getHelper();
        $components = $helper->generateSignature('app_verify_route', '1234', 'jr@rushlow.dev');

        self::assertSame($signature, $components->getSignature());
    }

    public function testIsValidSignature(): void
    {
        $timestamp = (new \DateTimeImmutable('+1 minutes'))->getTimestamp();
        $expires = 'expires='.$timestamp;

        $signature = '/?'.$expires.'&signature=abc';
        $uriToBeVerified = '/?'.$expires.'signature=abc&user=123&email=jr@rushlow.dev';

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('getExpiryTimeStamp')
            ->with($signature)
            ->willReturn($timestamp)
        ;

        $this->mockQueryUtility
            ->expects($this->once())
            ->method('addQueryParams')
            ->with(self::isInstanceOf(QueryParamCollection::class), $signature)
            ->willReturn($uriToBeVerified)
        ;

        $this->mockSigner
            ->expects($this->once())
            ->method('isValid')
            ->with($uriToBeVerified)
        ;

        $helper = $this->getHelper();
        $helper->isValidSignature($signature, '1234', 'jr@rushlow.dev');
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

    private function getHelper(): VerifyHelperInterface
    {
        return new VerifyHelper($this->mockRouter, $this->mockSigner, $this->mockQueryUtility, 3600);
    }
}
