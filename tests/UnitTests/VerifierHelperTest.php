<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\UnitTests;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyUser\Generator\TokenGenerator;
use SymfonyCasts\Bundle\VerifyUser\VerifyHelper;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifierHelperTest extends TestCase
{
    public function testIsSignature(): void
    {
        $this->markTestIncomplete('Not yet finished');
        $helper = new VerifyHelper(3600);
        $components = $helper->generateSignature('1234', 'jr@rushlow.dev');

        self::assertSame('', $components->getSignature());
    }

    public function testIsValid(): void
    {
        $helper = new VerifyHelper(3600);
        $components = $helper->generateSignature('1234', 'jr@rushlow.dev');

        $result = $helper->isValidSignature($components->getSignature(), '1234', 'jr@rushlow.dev');
        self::assertTrue($result);
    }
//    public function testUsesParamsToCreateComponents(): void
//    {
//        $userId = 'test-user';
//        $expiresAt = new \DateTimeImmutable();
//
//        $generator = $this->createMock(TokenGenerator::class);
//        $generator
//            ->expects($this->once())
//            ->method('getToken')
//            ->with($expiresAt, $userId)
//        ;
//
//        $helper = new VerifyHelper($generator, 100);
//        $helper->generateSignature($userId, $expiresAt);
//    }
//
//    public function testCreatesComponentsWithoutProvidingExpireDate(): void
//    {
//        $userId = 'test-user';
//
//        $generator = $this->createMock(TokenGenerator::class);
//        $generator
//            ->expects($this->once())
//            ->method('getToken')
//            ->with(self::isInstanceOf(\DateTimeInterface::class), $userId)
//        ;
//
//        $helper = new VerifyHelper($generator, 100);
//        $helper->generateSignature($userId);
//    }
//
//    public function testIsValid(): void
//    {
//        $expiresAt = new \DateTimeImmutable('2020-01-01 12:00');
//        $userId = 'test-user';
//
//        $token = \hash_hmac('sha256', \json_encode([$expiresAt, $userId]), '1234', false);
//        $signature = $expiresAt->getTimestamp().$token;
//
//        $mockGenerator = $this->createMock(TokenGenerator::class);
//        $mockGenerator
//            ->expects($this->once())
//            ->method('getToken')
//            ->with($expiresAt, $userId)
//            ->willReturn($token)
//        ;
//
//        $helper = new VerifyHelper($mockGenerator, 100);
//        self::assertTrue($helper->isValidSignature($signature, $userId));
//    }
}
