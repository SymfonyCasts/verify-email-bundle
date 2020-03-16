<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Exception;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class ExpiredSignatureException extends \Exception implements VerifyUserExceptionInterface
{
    public function getReason(): string
    {
        return 'The link to verify your email has expired. Please request a new link.';
    }
}
