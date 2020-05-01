<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Exception;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class AlreadyVerifiedException extends \Exception implements VerifyEmailExceptionInterface
{
    public function getReason(): string
    {
        return 'Your email address has already been verified.';
    }
}
