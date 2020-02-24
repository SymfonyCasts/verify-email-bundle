<?php

namespace SymfonyCasts\Bundle\VerifyUser\Tests\UnitTests\Generator;

use SymfonyCasts\Bundle\VerifyUser\Generator\TokenGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class TokenGeneratorTest extends TestCase
{
    public function testReturnsTokenWithEncodedParams(): void
    {
        $key = 'abc';
        $user = 1234;
        $expiresAt = new \DateTimeImmutable();

        $expected = \hash_hmac(
            'sha256',
            \json_encode([$expiresAt->getTimestamp(), $user]),
            $key,
            false
        );

        $generator = new TokenGenerator($key);
        $result = $generator->getToken($expiresAt, $user);

        self::assertSame($expected, $result);
    }
}
