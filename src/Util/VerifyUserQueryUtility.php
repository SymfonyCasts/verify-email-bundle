<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Util;

use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserQueryParam;

/**
 * Provides methods to manipulate a query string in a URI.
 *
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 * @final
 */
class VerifyUserQueryUtility
{
    /**
     * @param VerifyUserQueryParam[] $queryParams
     */
    public function addQueryParams(array $queryParams, string $uri): string
    {
        $parsedUri = parse_url($uri);
        $params = [];

        if (isset($parsedUri['query'])) {
            parse_str($parsedUri['query'], $params);
        }

        foreach ($queryParams as $param) {
            $params[$param->getKey()] = $param->getValue();
        }

        $path = $parsedUri['path'] ?? '';

        return $path.'?'.$this->getSortedQueryString($params);
    }

    //@TODO remove/add method handle full uri? hmm [scheme] etc.. hmmm let me think

    /**
     * @param VerifyUserQueryParam[] $queryParams
     */
    public function removeQueryParam(array $queryParams, string $uri): string
    {
        $parsedUri = parse_url($uri);
        $params = [];

        if (isset($parsedUri['query'])) {
            parse_str($parsedUri['query'], $params);
        }

        foreach ($queryParams as $param) {
            if (isset($params[$param->getKey()])) {
                unset($params[$param->getKey()]);
            }
        }

        $path = $parsedUri['path'] ?? '';

        return $path.'?'.$this->getSortedQueryString($params);
    }

    public function getExpiryTimeStamp(string $uri): int
    {
        $parsedUri = parse_url($uri);

        if (!isset($parsedUri['query'])) {
            return 0;
        }

        parse_str($parsedUri['query'], $params);

        return (int) $params['expires'];
    }

    private function getSortedQueryString(array $params): string
    {
        ksort($params);

        return http_build_query($params);
    }
}
