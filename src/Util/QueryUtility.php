<?php

namespace SymfonyCasts\Bundle\VerifyUser\Util;

use SymfonyCasts\Bundle\VerifyUser\Collection\QueryParamCollection;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 * @final
 */
class QueryUtility
{
    public function removeQueryParam(QueryParamCollection $collection, string $uri): string
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

    public function addQueryParams(QueryParamCollection $collection, string $uri): string
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

    public function getExpiryTimeStamp(string $uri): ?string
    {
        $parsedUri = \parse_url($uri);

        if (!isset($parsedUri['query'])) {
            return null;
        }

        \parse_str($parsedUri['query'], $params);

        return $params['expires'] ?? null;
    }

    private function getSortedQueryString(array $params): string
    {
        \ksort($params);

        return \http_build_query($params);
    }
}
