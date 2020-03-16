<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Util;

use SymfonyCasts\Bundle\VerifyUser\Collection\VerifyUserQueryParamCollection;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 * @final
 */
class VerifyUserQueryUtility
{
    public function removeQueryParam(VerifyUserQueryParamCollection $collection, string $uri): string
    {
        $parsedUri = \parse_url($uri);
        \parse_str($parsedUri['query'], $params);

        foreach ($collection as $queryParam) {
            if (isset($params[$queryParam->getKey()])) {
                unset($params[$queryParam->getKey()]);
            }
        }

        return $parsedUri['path'].'?'.$this->getSortedQueryString($params);
    }

    public function addQueryParams(VerifyUserQueryParamCollection $collection, string $uri): string
    {
        $parsedUri = \parse_url($uri);
        $params = [];
        if (isset($parsedUri['query'])) {
            \parse_str($parsedUri['query'], $params);
        }

        foreach ($collection as $queryParam) {
            $params[$queryParam->getKey()] = $queryParam->getValue();
        }

        return $parsedUri['path'].'?'.$this->getSortedQueryString($params);
    }

    public function getExpiryTimeStamp(string $uri): int
    {
        $parsedUri = \parse_url($uri);

        if (!isset($parsedUri['query'])) {
            return 0;
        }

        \parse_str($parsedUri['query'], $params);

        return (int) $params['expires'];
    }

    private function getSortedQueryString(array $params): string
    {
        \ksort($params);

        return \http_build_query($params);
    }
}
