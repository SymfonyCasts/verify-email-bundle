<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Tests\UnitTests\Util;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserUrlComponents;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserQueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserUrlUtility;

class VerifyUserQueryTest extends TestCase
{
    /**
     * @var MockObject|VerifyUserUrlUtility
     */
    private $mockUrlUtility;

    protected function setUp(): void
    {
        $this->mockUrlUtility = $this->createMock(VerifyUserUrlUtility::class);
    }

    public function testGetsExpiryTimeFromQueryString(): void
    {
        $uri = '/?a=x&expires=1234567890';

        $components = new VerifyUserUrlComponents();
        $components->setPath('/');
        $components->setQuery('a=x&expires=1234567890');

        $this->mockUrlUtility
            ->expects($this->once())
            ->method('parseUrl')
            ->with($uri)
            ->willReturn($components)
        ;

        $queryUtility = new VerifyUserQueryUtility($this->mockUrlUtility);
        $result = $queryUtility->getExpiryTimeStamp($uri);

        self::assertSame(
            1234567890,
            $result
        );
    }
}
