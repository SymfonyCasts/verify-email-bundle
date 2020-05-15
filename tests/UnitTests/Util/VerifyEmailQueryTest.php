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

class VerifyEmailQueryTest extends TestCase
{
    public function testGetsExpiryTimeFromQueryString(): void
    {
        $uri = '/?a=x&expires=1234567890';

        $queryUtility = new VerifyEmailQueryUtility();
        $result = $queryUtility->getExpiryTimestamp($uri);

        self::assertSame(
            1234567890,
            $result
        );
    }
}
