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
 * Provides methods to manipulate a query string in a URI.
 *
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 *
 * @internal
 * @final
 */
class VerifyEmailQueryUtility
{
    public function getExpiryTimestamp(string $uri): int
    {
        $params = $this->getQueryParams($uri);

        if (empty($params['expires'])) {
            return 0;
        }

        return (int) $params['expires'];
    }

    private function getQueryParams(string $uri): array
    {
        $params = [];
        $queryString = $this->parseUrl($uri);
//        $queryString = $components->getQuery();

        if (null !== $queryString) {
            parse_str($queryString, $params);
        }

        return $params;
    }

    private function parseUrl(string $url): ?string
    {
        $urlComponents = parse_url($url);

//        $components = new VerifyEmailUrlComponents();

        //@TODO native array_key_exists would make more sense here.
        foreach ($urlComponents as $component => $value) {
//            $method = 'set'.ucfirst($component);
//            $components->$method($value);

            if ('query' === $component) {
                return $value;
            }
        }

        return null;
    }
}
