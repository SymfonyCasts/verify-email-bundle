<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use SymfonyCasts\Bundle\VerifyUser\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserSignatureComponents;

/**
 * @author  Jesse Rushlow <jr@rushlow.dev>
 */
interface VerifyUserHelperInterface
{
    /**
     * Generate a signed URI that can be used to validate a user.
     */
    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParameters = []): VerifyUserSignatureComponents;

    /**
     * Validate a signed URI and mark the user a verified.
     *
     * @throws ExpiredSignatureException
     */
    public function isValidSignature(string $signature, string $userId, string $userEmail): bool;

    /**
     * Returns the length of time in seconds that a signed uri is valid.
     */
    public function getSignatureLifetime(): int;
}
