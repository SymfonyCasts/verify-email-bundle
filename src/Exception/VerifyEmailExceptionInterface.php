<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Exception;

/**
 * An exception that is thrown by VerifyEmailHelperInterface::validateEmailConfirmation().
 *
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
interface VerifyEmailExceptionInterface extends \Throwable
{
    /**
     * Returns a safe string that describes why verification failed.
     */
    public function getReason(): string;
}
