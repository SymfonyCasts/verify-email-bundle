<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyUser\Collection\QueryParamCollection;
use SymfonyCasts\Bundle\VerifyUser\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyUser\Model\QueryParam;
use SymfonyCasts\Bundle\VerifyUser\Model\SignatureComponents;
use SymfonyCasts\Bundle\VerifyUser\Util\QueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\UriSigningWrapper;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifyHelper implements VerifyHelperInterface
{
    private $router;
    private $uriSigner;
    private $queryUtility;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;

    public function __construct(UrlGeneratorInterface $router, UriSigningWrapper $uriSigner, QueryUtility $queryUtility, int $lifetime)
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->lifetime = $lifetime;
    }

    // @TODO This will get past the URI string from the app.
    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParams = []): SignatureComponents
    {
        $uri = $this->router->generate($routeName, $extraParams);

        $expiresAt = new \DateTimeImmutable(\sprintf('+%d seconds', $this->lifetime));

        $collection = new QueryParamCollection();
        $collection->createParam(QueryParam::USER_ID, $userId);
        $collection->createParam(QueryParam::USER_EMAIL, $userEmail);
        $collection->createParam(QueryParam::EXPIRES_AT, (string) $expiresAt->getTimestamp());

        $toBeSigned = $this->queryUtility->addQueryParams($collection, $uri);

        $collection->offsetUnset(2);

        $piiRemovedFromSignature = $this->queryUtility->removeQueryParam($collection, $this->uriSigner->signUri($toBeSigned));

        return new SignatureComponents($expiresAt, $piiRemovedFromSignature);
    }

    /**
     * @throws ExpiredSignatureException
     */
    public function isValidSignature(string $signature, string $userId, string $userEmail): bool
    {
        // check time is not expired here / if true exit early...
        $timestamp = $this->queryUtility->getExpiryTimeStamp($signature);

        if ($timestamp <= \time()) {
            throw new ExpiredSignatureException();
        }

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
