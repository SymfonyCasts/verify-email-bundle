<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail;

use Symfony\Component\HttpKernel\UriSigner;
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
    private $uriSigner;
    private $queryUtility;
    private $tokenGenerator;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;
    private $useRelativePath;

    public function __construct(UrlGeneratorInterface $router, UriSigner $uriSigner, VerifyEmailQueryUtility $queryUtility, VerifyEmailTokenGenerator $generator, int $lifetime, bool $useRelativePath)
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->tokenGenerator = $generator;
        $this->lifetime = $lifetime;
        $this->useRelativePath = $useRelativePath;
    }

    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParams = []): VerifyEmailSignatureComponents
    {
        $generatedAt = time();
        $expiryTimestamp = $generatedAt + $this->lifetime;

        $extraParams['token'] = $this->tokenGenerator->createToken($userId, $userEmail);
        $extraParams['expires'] = $expiryTimestamp;

        $uri = $this->router->generate($routeName, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL);

        /** @psalm-suppress PossiblyFalseArgument */
        return new VerifyEmailSignatureComponents(\DateTimeImmutable::createFromFormat('U', (string) $expiryTimestamp), $this->getSignedUrl($uri), $generatedAt);
    }

    public function validateEmailConfirmation(string $signedUrl, string $userId, string $userEmail): void
    {
        if (!$this->uriSigner->check($signedUrl)) {
            throw new InvalidSignatureException();
        }

        if ($this->queryUtility->getExpiryTimestamp($signedUrl) <= time()) {
            throw new ExpiredSignatureException();
        }

        $knownToken = $this->tokenGenerator->createToken($userId, $userEmail);
        $userToken = $this->queryUtility->getTokenFromQuery($signedUrl);

        if (!hash_equals($knownToken, $userToken)) {
            throw new WrongEmailVerifyException();
        }
    }

    private function generateAbsolutePath(string $absoluteUri): string
    {
        $parsedUri = parse_url($absoluteUri);

        $path = $parsedUri['path'] ?? '';
        $query = $this->getQueryStringFromParsedUrl($parsedUri);
        $fragment = isset($parsedUri['fragment']) ? '#'.$parsedUri['fragment'] : '';

        return $path.$query.$fragment;
    }

    public function generateSigningString(string $uri): string
    {
        if (!$this->useRelativePath) {
            return $uri;
        }

        return $this->generateAbsolutePath($uri);
    }

    private function generateBaseUrl(string $absoluteUri): string
    {
        $parsedUri = parse_url($absoluteUri);
        $scheme = isset($parsedUri['scheme']) ? $parsedUri['scheme'].'://' : '';
        $host = $parsedUri['host'] ?? '';

        return $scheme.$host;
    }

    private function getSignedUrl(string $uri): string
    {
        $signature = $this->uriSigner->sign($this->generateSigningString($uri));

        if (!$this->useRelativePath) {
            return $signature;
        }

        return $this->generateBaseUrl($uri).$signature;
    }

    private function getQueryStringFromParsedUrl(array $parsedUrl): string
    {
        if (!\array_key_exists('query', $parsedUrl)) {
            return '';
        }

        return $parsedUrl['query'] ? ('?'.$parsedUrl['query']) : '';
    }
}
