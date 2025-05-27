<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SymfonyCasts\Bundle\VerifyEmail\Factory;

use SensitiveParameter;
use Symfony\Component\HttpFoundation\UriSigner;

/**
 * @author Victor Bocharsky <victor@symfonycasts.com>
 * @author Ryan Weaver      <ryan@symfonycasts.com>
 *
 * Will become final && internal and ultimately removed in v2.0.
 *
 * @internal
 */
final readonly class UriSignerFactory
{
    public function __construct(
        #[SensitiveParameter]
        
        private string $secret,
        private string $parameter = '_hash',
    ) {
    }

    public function createUriSigner(): UriSigner
    {
        return new UriSigner($this->secret, $this->parameter);
    }
}
