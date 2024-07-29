<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\UnitTests\Model;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifyEmailSignatureComponentsTest extends TestCase
{
    public function testGetExpiresAtInterval(): void
    {
        $created = time();

        $expire = \DateTimeImmutable::createFromFormat('U', (string) ($created + 3600));

        $components = new VerifyEmailSignatureComponents($expire, 'some-uri', $created);

        self::assertSame(1, $components->getExpiresAtIntervalInstance()->h);
    }

    /**
     * @dataProvider translationIntervalDataProvider
     */
    public function testTranslations(int $lifetime, int $expectedInterval, string $unitOfMeasure): void
    {
        $created = time();

        $expire = \DateTimeImmutable::createFromFormat('U', (string) ($created + $lifetime));

        $components = new VerifyEmailSignatureComponents($expire, 'some-uri', $created);

        self::assertSame(
            \sprintf('%%count%% %s|%%count%% %ss', $unitOfMeasure, $unitOfMeasure),
            $components->getExpirationMessageKey()
        );

        self::assertSame(['%count%' => $expectedInterval], $components->getExpirationMessageData());
    }

    public function translationIntervalDataProvider(): \Generator
    {
        yield [60, 1, 'minute'];
        yield [900, 15, 'minute'];
        yield [3600, 1, 'hour'];
        yield [7200, 2, 'hour'];
        yield [43200, 12, 'hour'];
        yield [86400, 1, 'day'];
        yield [864000, 10, 'day'];
        yield [2678400, 1, 'month'];
        yield [5356800, 2, 'month'];
        yield [34819200, 1, 'year'];
    }
}
