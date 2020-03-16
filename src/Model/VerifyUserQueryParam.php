<?php

namespace SymfonyCasts\Bundle\VerifyUser\Model;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifyUserQueryParam
{
    public const USER_ID = 'id';
    public const USER_EMAIL = 'email';
    public const EXPIRES_AT = 'expires';

    private $key;

    private $value;

    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
