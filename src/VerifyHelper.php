<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use SymfonyCasts\Bundle\VerifyUser\Collection\QueryParamCollection;
use SymfonyCasts\Bundle\VerifyUser\Model\QueryParam;
use SymfonyCasts\Bundle\VerifyUser\Model\SignatureComponents;
use SymfonyCasts\Bundle\VerifyUser\Util\QueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\UriSigningWrapper;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifyHelper implements VerifyHelperInterface
{
    private $uriSigner;
    private $queryUtility;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;

    public function __construct(UriSigningWrapper $uriSigner, QueryUtility $queryUtility, int $lifetime)
    {
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->lifetime = $lifetime;
    }

    // @TODO This will get past the URI string from the app.
    public function generateSignature(string $userId, string $userEmail, \DateTimeInterface $expiresAt = null): SignatureComponents
    {
        // @TODO - Do I even need to accept an expiresAt Argument?
        if (null === $expiresAt) {
            $expiresAt = new \DateTimeImmutable(\sprintf('+%d seconds', $this->lifetime));
        }

        $collection = new QueryParamCollection();
        $collection->createParam(QueryParam::USER_ID, $userId);
        $collection->createParam(QueryParam::USER_EMAIL, $userEmail);
        $collection->createParam(QueryParam::EXPIRES_AT, (string) $expiresAt->getTimestamp());

        $toBeSigned = $this->queryUtility->addQueryParams($collection, '/');

        $collection->offsetUnset(2);

        $piiRemovedFromSignature = $this->queryUtility->removeQueryParam($collection, $this->uriSigner->signUri($toBeSigned));

        return new SignatureComponents($expiresAt, $piiRemovedFromSignature);
    }

    public function isValidSignature(string $signature, string $userId, string $userEmail): bool
    {
        // check time is not expired here / if true exit early...
        $timestamp = (int) $this->queryUtility->getExpiryTimeStamp($signature);

        $collection = new QueryParamCollection();
        $collection->createParam(QueryParam::USER_ID, $userId);
        $collection->createParam(QueryParam::USER_EMAIL, $userEmail);

        $uriToCheck = $this->queryUtility->addQueryParams($collection, $signature);

        return $this->uriSigner->isValid($uriToCheck);
    }

    public function getSignatureLifetime(): int
    {
        return $this->lifetime;
    }
}
