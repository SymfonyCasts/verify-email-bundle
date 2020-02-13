<?php

namespace JRushlow\Bundle\VerifyUser\Generator;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
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
     * Get a cryptographically secure token
     *
     * @param mixed  $userId   Unique user identifier
     */
    public function getToken(\DateTimeInterface $expiresAt, $userId): string
    {
        $encodedData = \json_encode([$expiresAt->getTimestamp(), $userId]);

        return \hash_hmac('sha256', $encodedData, $this->signingKey, false);
    }
}