<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SymfonyCasts\Bundle\VerifyEmail\Exception;

use RuntimeException;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class VerifyEmailRuntimeException extends RuntimeException implements VerifyEmailExceptionInterface
{
    public function getReason(): string
    {
        return $this->getMessage();
    }
}
