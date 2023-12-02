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
 */
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
    public function createUriSigner(): object
    {
        if (class_exists(UriSigner::class)) {
            return new UriSigner($this->secret, $this->parameter);
        }

        return new LegacyUriSigner($this->secret, $this->parameter);
    }
}
