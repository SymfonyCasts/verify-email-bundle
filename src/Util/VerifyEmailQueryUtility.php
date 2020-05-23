<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Util;

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
    public function getTokenFromQuery(string $uri): string
    {
        $params = $this->getQueryParams($uri);

        return $params['token'];
    }

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
        $urlComponents = parse_url($uri);

        if (\array_key_exists('query', $urlComponents)) {
            parse_str(($urlComponents['query'] ?? ''), $params);
        }

        return $params;
    }
}
