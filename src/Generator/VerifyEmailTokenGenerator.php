<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Generator;

use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailRuntimeException;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @internal
 *
 * @final
 */
class VerifyEmailTokenGenerator
{
    /**
     * @param string $signingKey Unique, random, cryptographically secure string
     */
    public function __construct(
        #[\SensitiveParameter]
        private string $signingKey
    ) {
    }

    /**
     * Get a cryptographically secure token.
     *
     * @throws VerifyEmailRuntimeException
     */
    public function createToken(string $userId, string $email): string
    {
        try {
            $encodedData = json_encode([$userId, $email], \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new VerifyEmailRuntimeException(message: 'Unable to create token. Invalid JSON.', previous: $exception);
        }

        return base64_encode(hash_hmac('sha256', $encodedData, $this->signingKey, true));
    }
}
