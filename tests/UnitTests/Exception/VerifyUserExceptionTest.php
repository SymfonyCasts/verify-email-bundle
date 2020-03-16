<?php

namespace SymfonyCasts\Bundle\VerifyUser\Tests\UnitTests\Exception;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyUser\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyUser\Exception\VerifyUserExceptionInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class VerifyUserExceptionTest extends TestCase
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
        $result = new $exception();
        self::assertSame($message, $result->getReason());
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testImplementsVerifyUserExceptionInterface(string $exception): void
    {
        $interfaces = \class_implements($exception);
        self::assertArrayHasKey(VerifyUserExceptionInterface::class, $interfaces);
    }
}
