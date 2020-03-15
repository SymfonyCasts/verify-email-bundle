<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use SymfonyCasts\Bundle\VerifyUser\DependencyInjection\SymfonyCastsVerifyUserExtension;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class SymfonyCastsVerifyUserBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new SymfonyCastsVerifyUserExtension();
        }

        return $this->extension ?: null;
    }
}
