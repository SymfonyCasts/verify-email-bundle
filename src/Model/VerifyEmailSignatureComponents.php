<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Model;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailSignatureComponents
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
     * Returns the full signed URL that should be sent to the user.
     */
    public function getSignedUrl(): string
    {
        return $this->uri;
    }

    /**
     * Get the length of time in seconds that a signature is valid for.
     */
    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}
