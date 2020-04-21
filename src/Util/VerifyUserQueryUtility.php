<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Util;

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
    private $urlUtility;

    public function __construct(VerifyUserUrlUtility $urlUtility)
    {
        $this->urlUtility = $urlUtility;
    }

    public function getTokenFromQuery(string $uri): string
    {
        $params = $this->getQueryParams($uri);

        return $params['token'];
    }

    public function getExpiryTimeStamp(string $uri): int
    {
        //@TODO - validate timestamp before return
        $params = $this->getQueryParams($uri);

        if (empty($params['expires'])) {
            return 0;
        }

        return (int) $params['expires'];
    }

    private function getQueryParams(string $uri): array
    {
        $params = [];
        $components = $this->urlUtility->parseUrl($uri);
        $queryString = $components->getQuery();

        if (null !== $queryString) {
            parse_str($queryString, $params);
        }

        return $params;
    }
}
