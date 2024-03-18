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
     * @var int expiresAt translator interval
     */
    private int $transInterval = 0;

    /**
     * @param int $generatedAt timestamp when the signature was created
     */
    public function __construct(
        private \DateTimeInterface $expiresAt,
        private string $uri,
        private int $generatedAt
    ) {
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

    /**
     * Get the translation message for when a signature expires.
     *
     * This is used in conjunction with the getExpirationMessageData() method.
     * Example usage in a Twig template:
     *
     * <p>{{ components.expirationMessageKey|trans(components.expirationMessageData) }}</p>
     *
     * symfony/translation is required to translate into a non-English locale.
     */
    public function getExpirationMessageKey(): string
    {
        $interval = $this->getExpiresAtIntervalInstance();

        switch ($interval) {
            case $interval->y > 0:
                $this->transInterval = $interval->y;

                return '%count% year|%count% years';
            case $interval->m > 0:
                $this->transInterval = $interval->m;

                return '%count% month|%count% months';
            case $interval->d > 0:
                $this->transInterval = $interval->d;

                return '%count% day|%count% days';
            case $interval->h > 0:
                $this->transInterval = $interval->h;

                return '%count% hour|%count% hours';
            default:
                $this->transInterval = $interval->i;

                return '%count% minute|%count% minutes';
        }
    }

    public function getExpirationMessageData(): array
    {
        $this->getExpirationMessageKey();

        return ['%count%' => $this->transInterval];
    }

    /**
     * Get the interval that the signature is valid for.
     *
     * @psalm-suppress PossiblyFalseArgument
     */
    public function getExpiresAtIntervalInstance(): \DateInterval
    {
        $createdAtTime = \DateTimeImmutable::createFromFormat('U', (string) $this->generatedAt);

        return $this->expiresAt->diff($createdAtTime);
    }
}
