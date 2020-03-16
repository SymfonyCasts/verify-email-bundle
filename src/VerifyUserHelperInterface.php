<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserSignatureComponents;

/**
 * @author  Jesse Rushlow <jr@rushlow.dev>
 */
interface VerifyUserHelperInterface
{
    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParameters = []): VerifyUserSignatureComponents;

    public function isValidSignature(string $signature, string $userId, string $userEmail): bool;

    public function getSignatureLifetime(): int;
}
