<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\UnitTests\Generator;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyUser\Generator\TokenGenerator;

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
