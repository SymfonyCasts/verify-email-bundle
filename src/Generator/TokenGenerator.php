<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Generator;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 * @final
 */
class TokenGenerator
{
    /**
     * @var string Unique, random, cryptographically secure string
     */
    private $signingKey;

    public function __construct(string $signingKey)
    {
        $this->signingKey = $signingKey;
    }

    /**
     * Get a cryptographically secure token.
     *
     * @param mixed $userId Unique user identifier
     */
    public function getToken(\DateTimeInterface $expiresAt, $userId): string
    {
        $encodedData = \json_encode([$expiresAt->getTimestamp(), $userId]);

        return \hash_hmac('sha256', $encodedData, $this->signingKey, false);
    }
}
