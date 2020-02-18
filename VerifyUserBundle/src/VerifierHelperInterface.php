<?php

namespace JRushlow\Bundle\VerifyUser;

use JRushlow\Bundle\VerifyUser\Model\SignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
interface VerifierHelperInterface
{
    public function getSignature(string $userId, \DateTimeInterface $expires): SignatureComponents;

    public function isValidSignature(string $signature, string $userId): bool;
}