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
    private $urlUtility;

    public function __construct(VerifyUserUrlUtility $urlUtility)
    {
        $this->urlUtility = $urlUtility;
    }

    public function getQueryString(string $uri): string
    {
        $components = $this->urlUtility->parseUrl($uri);

        return $components->getQuery();
    }

    public function getQueryParams(string $uri): array
    {
        $params = [];

        $queryString = $this->getQueryString($uri);
        parse_str($queryString, $params);

        return $params;
    }

    public function getTokenFromQuery(string $uri): string
    {
        $params = $this->getQueryParams($uri);

        return $params['token'];
    }

    /**
     * @param VerifyUserQueryParam[] $queryParams
     */
    public function addQueryParams(array $queryParams, string $uri): string
    {
        $urlComponents = $this->urlUtility->parseUrl($uri);
        $params = [];

        if (null !== ($queryString = $urlComponents->getQuery())) {
            parse_str($queryString, $params);
        }

        foreach ($queryParams as $param) {
            $params[$param->getKey()] = $param->getValue();
        }

        $urlComponents->setQuery($this->getSortedQueryString($params));

        return $this->urlUtility->buildUrl($urlComponents);
    }

    /**
     * @param VerifyUserQueryParam[] $queryParams
     */
    public function removeQueryParam(array $queryParams, string $uri): string
    {
        $urlComponents = $this->urlUtility->parseUrl($uri);
        $params = [];

        if (null !== ($queryString = $urlComponents->getQuery())) {
            parse_str($queryString, $params);
        }

        foreach ($queryParams as $param) {
            if (isset($params[$param->getKey()])) {
                unset($params[$param->getKey()]);
            }
        }

        $urlComponents->setQuery($this->getSortedQueryString($params));

        return $this->urlUtility->buildUrl($urlComponents);
    }

    public function getExpiryTimeStamp(string $uri): int
    {
        //@TODO - validate timestamp before return
        $components = $this->urlUtility->parseUrl($uri);

        if (null === ($query = $components->getQuery())) {
            return 0;
        }

        parse_str($query, $params);

        return (int) $params['expires'];
    }

    private function getSortedQueryString(array $params): string
    {
        ksort($params);

        return http_build_query($params);
    }
}
