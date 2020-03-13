<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use SymfonyCasts\Bundle\VerifyUser\Model\SignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
interface VerifyHelperInterface
{
    /**
     * Create a
     * @param string $userId Identifiable string that's unique to a user. (email, id, etc..)
     */
    public function generateSignature(string $userId, \DateTimeInterface $expires): SignatureComponents;

    /**
     * @param string $signature Timestamp + hashed token
     * @param string $userId    User identifier used to create hashed token (email, id, etc..)
     */
    public function isValidSignature(string $signature, string $userId): bool;

    /**
     * Get the lifetime of a signed URI in seconds.
     */
    public function getSignatureLifetime(): int;
}
