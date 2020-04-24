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

    public function buildUrl(VerifyEmailUrlComponents $components): string
    {
        // Order in which components must appear in a url with their separator
        $componentOrder = [
            'Scheme' => '://',
            'User' => null,
            'Host' => null,
            'Port' => ':',
            'Path' => null,
            'Query' => '?',
            'Fragment' => '#',
        ];

        $url = '';

        // Handle user first as it may have a password associated with it.
        $components = $this->formatCredentials($components);

        // Add components in order to the url string
        foreach ($componentOrder as $component => $separator) {
            $getter = 'get'.$component;
            $value = $components->$getter();

            if (null === $value) {
                continue;
            }

            if (null === $separator) {
                $url .= $value;
                continue;
            }

            if ('Scheme' !== $component) {
                $url .= $separator.$value;
                continue;
            }

            $url .= $value.$separator;
        }

        return $url;
    }

    private function formatCredentials(VerifyEmailUrlComponents $components): VerifyEmailUrlComponents
    {
        $user = $components->getUser();

        if (null !== $user && null !== ($pass = $components->getPass())) {
            $user .= ':'.$pass;
        }

        if (null !== $user) {
            $components->setUser($user.'@');
        }

        return $components;
    }
}
