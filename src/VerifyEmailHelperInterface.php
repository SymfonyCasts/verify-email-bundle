<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail;

use SymfonyCasts\Bundle\VerifyEmail\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
interface VerifyEmailHelperInterface
{
    /**
     * Get a signed Url that can be provided to a user.
     *
     * @param string $routeName   name of route that will be used to verify users
     * @param string $userId      unique user identifier
     * @param string $userEmail   the user's email address
     * @param array  $queryParams any additional query string parameters that will be a part of the signed URL
     */
    public function generateSignature(string $routeName, string $userId, string $userEmail, array $queryParams = []): VerifyEmailSignatureComponents;

    /**
     * Validate a signed Url provided by the user.
     *
     * @param string $signedUrl the URI that was submitted by the user
     * @param string $userId    unique user identifier
     * @param string $userEmail the user's unique email address
     *
     * @throws ExpiredSignatureException
     */
    public function isValidSignature(string $signedUrl, string $userId, string $userEmail): bool;

    /**
     * Get the length of time in seconds that a signed uri is valid.
     */
    public function getSignatureLifetime(): int;
}
