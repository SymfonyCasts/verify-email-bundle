<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Generator;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @internal
 * @final
 */
class VerifyEmailTokenGenerator
{
    /**
     * @var string Unique, random, cryptographically secure string
     */
    private $signingKey;

    public function __construct(string $key)
    {
        $this->signingKey = $key;
    }

    /**
     * Get a cryptographically secure token.
     */
    public function createToken(string $userId, string $email): string
    {
        $encodedData = json_encode([$userId, $email]);

        return base64_encode(hash_hmac('sha256', $encodedData, $this->signingKey, true));
    }
}
