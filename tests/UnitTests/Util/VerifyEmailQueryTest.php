<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\UnitTests\Util;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailQueryUtility;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @group legacy
 */
final class VerifyEmailQueryTest extends TestCase
{
    public function testGetsExpiryTimeFromQueryString(): void
    {
        $uri = '/?a=x&expires=1234567890';

        $queryUtility = new VerifyEmailQueryUtility();

        self::assertSame(1234567890, $queryUtility->getExpiryTimestamp($uri));
    }

    public function testGetsTokenFromQueryString(): void
    {
        $uri = 'https://symfonycasts.com/test?token=xyz';

        $queryUtil = new VerifyEmailQueryUtility();

        self::assertSame('xyz', $queryUtil->getTokenFromQuery($uri));
    }
}
