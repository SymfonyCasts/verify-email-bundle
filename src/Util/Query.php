<?php

namespace SymfonyCasts\Bundle\VerifyUser\Util;

use SymfonyCasts\Bundle\VerifyUser\Collection\QueryParamCollection;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 * @final
 */
class Query
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
        \parse_str($parsedUri['query'], $params);

        foreach ($collection as $queryParam) {
            $params[$queryParam->getKey()] = $queryParam->getValue();
        }

        return $parsedUri['path'].'?'.$this->getSortedQueryString($params);
    }

    private function getSortedQueryString(array $params): string
    {
        \ksort($params);

        return \http_build_query($params);
    }
}
