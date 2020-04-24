<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Model;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class VerifyUserSignatureComponents
{
    /**
     * @var \DateTimeInterface
     */
    private $expiresAt;

    /**
     * @var string
     */
    private $uri;

    public function __construct(\DateTimeInterface $expiresAt, string $uri)
    {
        $this->expiresAt = $expiresAt;
        $this->uri = $uri;
    }

    /**
     * Returns the full signed URI that a user should use.
     */
    public function getSignature(): string
    {
        return $this->uri;
    }

    /**
     * Get the length of time in seconds that a signature is valid for.
     */
    public function getExpiryTime(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}
