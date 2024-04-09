<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\Unit\Generator;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;

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
            json_encode(['1234', 'jr@rushlow.dev']),  /** @phpstan-ignore-line argument.type result is never false */
            'foo',
            true
        ));

        self::assertTrue(hash_equals(
            $knownHash,
            $generator->createToken('1234', 'jr@rushlow.dev'
            )));
    }
}
