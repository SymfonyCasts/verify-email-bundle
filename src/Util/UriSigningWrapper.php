<?php

namespace SymfonyCasts\Bundle\VerifyUser\Util;

use Symfony\Component\HttpKernel\UriSigner;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class UriSigningWrapper
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
