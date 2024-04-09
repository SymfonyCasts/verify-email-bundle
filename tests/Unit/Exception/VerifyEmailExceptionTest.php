<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyEmail\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailExceptionTest extends TestCase
{
    public function exceptionDataProvider(): \Generator
    {
        yield [
            ExpiredSignatureException::class,
            'The link to verify your email has expired. Please request a new link.',
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testIsReason(string $exception, string $message): void
    {
        /** @var VerifyEmailExceptionInterface $result */
        $result = new $exception();
        self::assertSame($message, $result->getReason());
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testImplementsVerifyEmailExceptionInterface(string $exception): void
    {
        self::assertIsArray($interfaces = class_implements($exception));
        self::assertArrayHasKey(VerifyEmailExceptionInterface::class, $interfaces);
    }
}
