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
    private $useRelativePath;

    public function __construct(UrlGeneratorInterface $router, /* no typehint for BC with legacy PHP */ $uriSigner, VerifyEmailQueryUtility $queryUtility, VerifyEmailTokenGenerator $generator, int $lifetime, bool $useRelativePath)
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->tokenGenerator = $generator;
        $this->lifetime = $lifetime;
        $this->useRelativePath = $useRelativePath;

        if (!$uriSigner instanceof UriSigner) {
            /** @psalm-suppress UndefinedFunction */
            @trigger_deprecation('symfonycasts/verify-email-bundle', '1.17.0', 'Not providing an instance of %s is deprecated. It will be required in v2.0', UriSigner::class);
        }
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
        /** @psalm-suppress UndefinedFunction */
        @trigger_deprecation('symfonycasts/verify-email-bundle', '1.17.0', '%s() is deprecated and will be removed in v2.0, use validateEmailConfirmationFromRequest() instead.', __METHOD__);

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

    public function validateEmailConfirmationFromRequest(Request $request, string $userId, string $userEmail): void
    {
        /** @legacy - Remove in 2.0 */
        if (!$this->uriSigner instanceof UriSigner) {
            throw new \RuntimeException(\sprintf('An instance of %s is required, provided by symfony/http-kernel >=6.4, to validate an email confirmation.', UriSigner::class));
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

    private function generateAbsolutePath(string $absoluteUri): string
    {
        $parsedUri = parse_url($absoluteUri);
        \assert(\is_array($parsedUri), 'Could not parse the provided URI.');

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

        if (false === $this->useRelativePath) {
            return $signature;
        }

        return $this->generateBaseUrl($uri).$signature;
    }

    /**
     * @param array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, query?: string, path?: string, fragment?: string} $parsedUrl
     */
    private function getQueryStringFromParsedUrl(array $parsedUrl): string
    {
        if (!\array_key_exists('query', $parsedUrl)) {
            return '';
        }

        return $parsedUrl['query'] ? ('?'.$parsedUrl['query']) : '';
    }
}
