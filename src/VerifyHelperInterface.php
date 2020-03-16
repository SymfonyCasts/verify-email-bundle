<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use SymfonyCasts\Bundle\VerifyUser\Model\SignatureComponents;

/**
 * @author  Jesse Rushlow <jr@rushlow.dev>
 */
interface VerifyHelperInterface
{
    public function generateSignature(string $userId, string $userEmail): SignatureComponents;

    public function isValidSignature(string $signature, string $userId, string $userEmail): bool;

    public function getSignatureLifetime(): int;
}
