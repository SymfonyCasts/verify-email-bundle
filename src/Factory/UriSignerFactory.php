<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Factory;

use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\UriSigner as LegacyUriSigner;

/**
 * @author Victor Bocharsky <victor@symfonycasts.com>
 * @author Ryan Weaver      <ryan@symfonycasts.com>
 *
 * Will become final && internal and ultimately removed in v2.0.
 *
 * @internal
 *
 * @final
 */
class UriSignerFactory
{
    public function __construct(
        #[\SensitiveParameter]
        private string $secret,
        private string $parameter = '_hash',
    ) {
    }

    /**
     * @return UriSigner|LegacyUriSigner
     */
    public function createUriSigner(): object
    {
        if (class_exists(UriSigner::class)) {
            return new UriSigner($this->secret, $this->parameter);
        }

        return new LegacyUriSigner($this->secret, $this->parameter);
    }
}
