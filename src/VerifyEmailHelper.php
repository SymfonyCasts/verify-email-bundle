<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\UriSigner as LegacyUriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\WrongEmailVerifyException;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailQueryUtility;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailHelper implements VerifyEmailHelperInterface
{
    private $router;
    /**
     * @var UriSigner|LegacyUriSigner
     */
    private $uriSigner;
    private $queryUtility;
    private $tokenGenerator;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;

    public function __construct(UrlGeneratorInterface $router, /* no typehint for BC with legacy PHP */ $uriSigner, VerifyEmailQueryUtility $queryUtility, VerifyEmailTokenGenerator $generator, int $lifetime)
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->tokenGenerator = $generator;
        $this->lifetime = $lifetime;
    }

    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParams = []): VerifyEmailSignatureComponents
    {
        $generatedAt = time();
        $expiryTimestamp = $generatedAt + $this->lifetime;

        $extraParams['token'] = $this->tokenGenerator->createToken($userId, $userEmail);
        $extraParams['expires'] = $expiryTimestamp;

        $uri = $this->router->generate($routeName, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL);

        $signature = $this->uriSigner->sign($uri);

        /** @psalm-suppress PossiblyFalseArgument */
        return new VerifyEmailSignatureComponents(\DateTimeImmutable::createFromFormat('U', (string) $expiryTimestamp), $signature, $generatedAt);
    }

    public function validateEmailConfirmation(string $signedUrl, string $userId, string $userEmail, ?Request $request = null): void
    {
        if (null === $request) {
            //@trigger_deprecation('You must pass a Request object....');
        }

        if ($hasRequestObject = $this->uriSigner instanceof UriSigner && null !== $request) {
            $isValid = $this->uriSigner->checkRequest($request);
        } else {
            // @trigger_deprecation('Don\'t use a string anymore');
            $isValid = $this->uriSigner->check($signedUrl);
        }

        if (!$isValid) {
            throw new InvalidSignatureException();
        }

        if ($hasRequestObject) {
            $expiresAt = $request->query->getInt('expires');
            $userToken = $request->query->getString('token');
        } else {
            $expiresAt = $this->queryUtility->getExpiryTimestamp($signedUrl);
            $userToken = $this->queryUtility->getTokenFromQuery($signedUrl);
        }

        if ($expiresAt <= time()) {
            throw new ExpiredSignatureException();
        }

        $knownToken = $this->tokenGenerator->createToken($userId, $userEmail);

        if (!hash_equals($knownToken, $userToken)) {
            throw new WrongEmailVerifyException();
        }
    }

    public function validateEmailConfirmationFromRequest(Request $request, string $userId, string $userEmail): void
    {
        if (!$this->uriSigner instanceof UriSigner) {
            throw new \RuntimeException('Use the other one instead');
        }

        if (!$this->uriSigner->checkRequest($request)) {
            throw new InvalidSignatureException();
        }

        if ($request->query->getInt('expires') <= time()) {
            throw new ExpiredSignatureException();
        }

        $knownToken = $this->tokenGenerator->createToken($userId, $userEmail);

        if (!hash_equals($knownToken, $request->query->getString('token'))) {
            throw new WrongEmailVerifyException();
        }
    }
}
