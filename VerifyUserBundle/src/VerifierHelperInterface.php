<?php

namespace JRushlow\Bundle\VerifyUser;

use JRushlow\Bundle\VerifyUser\Model\SignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
interface VerifierHelperInterface
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
}