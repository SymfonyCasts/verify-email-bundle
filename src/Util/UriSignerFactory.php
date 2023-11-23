<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Util;

use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\UriSigner as LegacyUriSigner;

class UriSignerFactory
{
    private $secret;
    private $parameter;

    public function __construct(string $secret, string $parameter = '_hash')
    {
        $this->secret = $secret;
        $this->parameter = $parameter;
    }

    /**
     * @return UriSigner|LegacyUriSigner
     */
    public function createUriSigner()
    {
        if (class_exists(UriSigner::class)) {
            return new UriSigner($this->secret, $this->parameter);
        }

        return new LegacyUriSigner($this->secret, $this->parameter);
    }
}
