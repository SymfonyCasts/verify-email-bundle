<?php

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\UnitTests\Generator;

use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @internal
 */
final class VerifyEmailTokenGeneratorTest extends TestCase
{
    public function testCreateToken(): void
    {
        $generator = new VerifyEmailTokenGenerator('foo');

        $knownHash = base64_encode(hash_hmac(
            'sha256',
            json_encode(['1234', 'jr@rushlow.dev']),
            'foo',
            true
        ));

        self::assertTrue(hash_equals(
            $knownHash,
            $generator->createToken('1234', 'jr@rushlow.dev'
        )));
    }
}
