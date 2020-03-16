<?php

namespace SymfonyCasts\Bundle\VerifyUser\Exception;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class ExpiredSignatureException extends \Exception implements VerifyUserExceptionInterface
{
    public function getReason(): string
    {
        return 'The link to verify your email has expired. Please request a new link.';
    }
}
