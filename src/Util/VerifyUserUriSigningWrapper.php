<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Util;

use Symfony\Component\HttpKernel\UriSigner;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 * @final
 */
class VerifyUserUriSigningWrapper
{
    /**
     * @var UriSigner
     */
    private $signer;

    public function __construct(string $signingKey)
    {
        $this->initialize($signingKey);
    }

    //@TODO - May not need this class
    private function initialize(string $signingKey): void
    {
        $this->signer = new UriSigner($signingKey, 'signature');
    }

    public function signUri(string $uri): string
    {
        return $this->signer->sign($uri);
    }

    public function isValid(string $uri): bool
    {
        return $this->signer->check($uri);
    }
}
