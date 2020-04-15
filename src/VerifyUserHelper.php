<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyUser\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyUser\Generator\VerifyUserTokenGenerator;
use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserSignatureComponents;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserQueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\VerifyUserUriSigningWrapper;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
final class VerifyUserHelper implements VerifyUserHelperInterface
{
    private $router;
    private $uriSigner;
    private $queryUtility;
    private $tokenGenerator;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;

    public function __construct(UrlGeneratorInterface $router, VerifyUserUriSigningWrapper $uriSigner, VerifyUserQueryUtility $queryUtility, VerifyUserTokenGenerator $generator, int $lifetime)
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->tokenGenerator = $generator;
        $this->lifetime = $lifetime;
    }

    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParams = []): VerifyUserSignatureComponents
    {
        $expiresAt = new \DateTimeImmutable(sprintf('+%d seconds', $this->lifetime));

        $extraParams['token'] = $this->tokenGenerator->createToken($userId, $userEmail, $expiresAt->getTimestamp());
        $extraParams['expires'] = $expiresAt->getTimestamp();

        $uri = $this->router->generate($routeName, $extraParams);
        $signature = $this->uriSigner->signUri($uri);

        return new VerifyUserSignatureComponents($expiresAt, $signature);
    }

    /**
     * @throws ExpiredSignatureException
     */
    public function isValidSignature(string $signature, string $userId, string $userEmail): bool
    {
        $expiresAt = $this->queryUtility->getExpiryTimeStamp($signature);

        if ($expiresAt <= time()) {
            throw new ExpiredSignatureException();
        }

        $knownToken = $this->tokenGenerator->createToken($userId, $userEmail, $expiresAt);

        parse_str($this->queryUtility->getQueryString($signature), $userProvidedParams);

        $userToken = $userProvidedParams['token'];

        if (!hash_equals($knownToken, $userToken)) {
            return false;
        }

        $params['token'] = $knownToken;
        $params['expires'] = $expiresAt;

        //@TODO we are not validating the signature correctly here

        return $this->uriSigner->isValid($signature);
    }

    public function getSignatureLifetime(): int
    {
        return $this->lifetime;
    }
}
