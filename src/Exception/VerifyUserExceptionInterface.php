<?php

namespace SymfonyCasts\Bundle\VerifyUser\Exception;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
interface VerifyUserExceptionInterface
{
    public function getReason(): string;
}
