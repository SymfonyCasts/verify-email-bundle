<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyUser\Collection\VerifyUserQueryParamCollection;
use SymfonyCasts\Bundle\VerifyUser\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserQueryParam;
use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserSignatureComponents;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserQueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserUriSigningWrapper;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifyUserHelper implements VerifyUserHelperInterface
{
    private $router;
    private $uriSigner;
    private $queryUtility;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;

    public function __construct(UrlGeneratorInterface $router, VerifyUserUriSigningWrapper $uriSigner, VerifyUserQueryUtility $queryUtility, int $lifetime)
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->lifetime = $lifetime;
    }

    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParams = []): VerifyUserSignatureComponents
    {
        $expiresAt = new \DateTimeImmutable(\sprintf('+%d seconds', $this->lifetime));

        $collection = new VerifyUserQueryParamCollection();
        $collection->createParam(VerifyUserQueryParam::USER_ID, $userId);
        $collection->createParam(VerifyUserQueryParam::USER_EMAIL, $userEmail);
        $collection->createParam(VerifyUserQueryParam::EXPIRES_AT, (string) $expiresAt->getTimestamp());

        foreach ($extraParams as $key => $value) {
            $collection->createParam($key, $value);
        }

        $toBeSigned = $this->queryUtility->addQueryParams(
            $collection,
            $this->router->generate($routeName, $extraParams)
        );

        $collection->offsetUnset(2);

        $piiRemovedFromSignature = $this->queryUtility->removeQueryParam($collection, $this->uriSigner->signUri($toBeSigned));

        return new VerifyUserSignatureComponents($expiresAt, $piiRemovedFromSignature);
    }

    /**
     * @throws ExpiredSignatureException
     */
    public function isValidSignature(string $signature, string $userId, string $userEmail): bool
    {
        if ($this->queryUtility->getExpiryTimeStamp($signature) <= \time()) {
            throw new ExpiredSignatureException();
        }

        $collection = new VerifyUserQueryParamCollection();
        $collection->createParam(VerifyUserQueryParam::USER_ID, $userId);
        $collection->createParam(VerifyUserQueryParam::USER_EMAIL, $userEmail);

        $uriToCheck = $this->queryUtility->addQueryParams($collection, $signature);

        return $this->uriSigner->isValid($uriToCheck);
    }

    public function getSignatureLifetime(): int
    {
        return $this->lifetime;
    }
}
