<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
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

    public function getSignature(): string
    {
        return $this->uri;
    }

    public function getExpiryTime(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}
