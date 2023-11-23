<?php

namespace SymfonyCasts\Bundle\VerifyEmail\Util;

use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\UriSigner as LegacyUriSigner;

class UriSignerFactory
{
    private $secret;
    private $parameter;

    public function __construct(#[\SensitiveParameter] string $secret, string $parameter = '_hash')
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
