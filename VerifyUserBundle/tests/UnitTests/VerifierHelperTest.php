<?php

namespace JRushlow\Bundle\Tests\UnitTests;

use JRushlow\Bundle\VerifyUser\Generator\TokenGenerator;
use JRushlow\Bundle\VerifyUser\VerifierHelper;
use PHPUnit\Framework\TestCase;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifierHelperTest extends TestCase
{
    public function testUsesParamsToCreateComponents(): void
    {
        $userId = 'test-user';
        $expiresAt = new \DateTimeImmutable();

        $generator = $this->createMock(TokenGenerator::class);
        $generator
            ->expects($this->once())
            ->method('getToken')
            ->with($expiresAt, $userId)
        ;

        $helper = new VerifierHelper($generator);
        $helper->getSignature($userId, $expiresAt);
    }

    public function testCreatesComponentsWithoutProvidingExpireDate(): void
    {
        $userId = 'test-user';

        $generator = $this->createMock(TokenGenerator::class);
        $generator
            ->expects($this->once())
            ->method('getToken')
            ->with(self::isInstanceOf(\DateTimeInterface::class), $userId)
        ;

        $helper = new VerifierHelper($generator);
        $helper->getSignature($userId);
    }

    public function testIsValid(): void
    {
        $expiresAt = new \DateTimeImmutable('2020-01-01 12:00');
        $userId = 'test-user';

        $signature = $expiresAt->getTimestamp() . \hash_hmac('sha256', \json_encode([$expiresAt, $userId]), '1234', false);

        $mockGenerator = $this->createMock(TokenGenerator::class);
        $mockGenerator
            ->expects($this->once())
            ->method('getToken')
            ->with($expiresAt, $userId)
            ->willReturn($signature)
        ;

        $helper = new VerifierHelper($mockGenerator);
        self::assertTrue($helper->isValidSignature($signature, $userId));
    }
}
