<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use Symfony\Component\HttpKernel\UriSigner;
use SymfonyCasts\Bundle\VerifyUser\Collection\QueryParamCollection;
use SymfonyCasts\Bundle\VerifyUser\Model\QueryParam;
use SymfonyCasts\Bundle\VerifyUser\Model\SignatureComponents;
use SymfonyCasts\Bundle\VerifyUser\Util\Query;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifyHelper
{
    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;

    public function __construct(int $lifetime)
    {
        $this->lifetime = $lifetime;
    }

    public function generateSignature(string $userId, string $userEmail, \DateTimeInterface $expiresAt = null): SignatureComponents
    {
        if (null === $expiresAt) {
            $expiresAt = (new \DateTimeImmutable('now'))
                ->modify(\sprintf('+%d seconds', 450));
        }

        $collection = new QueryParamCollection();
        //@TODO - add() should create the query object within the collection.
        $collection->add(new QueryParam(QueryParam::USER_ID, $userId));
        $collection->add(new QueryParam(QueryParam::USER_EMAIL, $userEmail));
        $collection->add(new QueryParam(QueryParam::EXPIRES_AT, $expiresAt->getTimestamp()));

        $queryUtil = new Query();

        $toBeSigned = $queryUtil->addQueryParams($collection, '/');

        $signer = new UriSigner('secret', 'signature');

        $collection->offsetUnset(2);

        $piiRemovedFromSignature = $queryUtil->removeQueryParam($collection, $signer->sign($toBeSigned));

        return new SignatureComponents($expiresAt, $piiRemovedFromSignature);
    }

    public function isValidSignature(string $signature, string $userId, string $userEmail): bool
    {
        $queryUtil = new Query();

        // check time is not expired here / if true exit early...
        $timestamp = (int) $queryUtil->getExpiryTimeStamp($signature);

        $collection = new QueryParamCollection();
        $collection->add(new QueryParam(QueryParam::USER_ID, $userId));
        $collection->add(new QueryParam(QueryParam::USER_EMAIL, $userEmail));

        $uriToCheck = $queryUtil->addQueryParams($collection, $signature);

        $signer = new UriSigner('secret', 'signature');

        return $signer->check($uriToCheck);
    }

    public function getSignatureLifetime(): int
    {
        return $this->lifetime;
    }
}
