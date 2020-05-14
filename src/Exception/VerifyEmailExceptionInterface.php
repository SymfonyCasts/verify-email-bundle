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
interface VerifyEmailExceptionInterface extends \Throwable
{
    public function getReason(): string;
}
