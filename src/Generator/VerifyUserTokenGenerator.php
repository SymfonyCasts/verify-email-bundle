<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Generator;

class VerifyUserTokenGenerator
{
    private $signingKey;

    public function __construct(string $key)
    {
        $this->signingKey = $key;
    }

    public function createToken(string $userId, string $email, bool $isVerified, int $expiryTimeStamp): string
    {
        $encodedData = json_encode([$userId, $email, $isVerified, $expiryTimeStamp]);

        return base64_encode(hash_hmac('sha256', $encodedData, $this->signingKey, true));
    }
}
