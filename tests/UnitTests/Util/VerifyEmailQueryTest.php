<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\UnitTests\Util;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailUrlComponents;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailQueryUtility;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailUrlUtility;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailQueryTest extends TestCase
{
    /**
     * @var MockObject|VerifyEmailUrlUtility
     */
    private $mockUrlUtility;

    protected function setUp(): void
    {
        $this->mockUrlUtility = $this->createMock(VerifyEmailUrlUtility::class);
    }

    public function testGetsExpiryTimeFromQueryString(): void
    {
        $uri = '/?a=x&expires=1234567890';

        $components = new VerifyEmailUrlComponents();
        $components->setPath('/');
        $components->setQuery('a=x&expires=1234567890');

        $this->mockUrlUtility
            ->expects($this->once())
            ->method('parseUrl')
            ->with($uri)
            ->willReturn($components)
        ;

        $queryUtility = new VerifyEmailQueryUtility($this->mockUrlUtility);
        $result = $queryUtility->getExpiryTimestamp($uri);

        self::assertSame(
            1234567890,
            $result
        );
    }
}
