<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Util;

use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailUrlComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @internal
 * @final
 */
class VerifyEmailUrlUtility
{
    public function parseUrl(string $url): VerifyEmailUrlComponents
    {
        $urlComponents = parse_url($url);

        $components = new VerifyEmailUrlComponents();

        foreach ($urlComponents as $component => $value) {
            $method = 'set'.ucfirst($component);
            $components->$method($value);
        }

        return $components;
    }
}
