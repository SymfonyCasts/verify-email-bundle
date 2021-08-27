<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use SymfonyCasts\Bundle\VerifyEmail\DependencyInjection\SymfonyCastsVerifyEmailExtension;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
class SymfonyCastsVerifyEmailBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new SymfonyCastsVerifyEmailExtension();
        }

        return $this->extension;
    }
}
