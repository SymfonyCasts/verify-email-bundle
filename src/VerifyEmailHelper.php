<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpFoundation\UrlHelper;
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
     * @var UriSigner
     *
     * @legacy - Until 2.0 this can also be a Symfony\Component\HttpKernel\UriSigner, which has the same API
     */
    private $uriSigner;
    private $queryUtility;
    private $tokenGenerator;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;
    /** @var bool */
    private $signWithoutHost;

    public function __construct(UrlGeneratorInterface $router, /* no typehint for BC with legacy PHP */ $uriSigner, VerifyEmailQueryUtility $queryUtility, VerifyEmailTokenGenerator $generator, int $lifetime, bool $signWithoutHost = false)
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->tokenGenerator = $generator;
        $this->lifetime = $lifetime;
        $this->signWithoutHost = $signWithoutHost;

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

        $signature = $this->signWithoutHost
            ? $this->signWithoutHost($routeName, $extraParams)
            : $this->signWithHost($routeName, $extraParams);

        /** @psalm-suppress PossiblyFalseArgument */
        return new VerifyEmailSignatureComponents(\DateTimeImmutable::createFromFormat('U', (string) $expiryTimestamp), $signature, $generatedAt);
    }

    public function validateEmailConfirmation(string $signedUrl, string $userId, string $userEmail): void
    {
        /** @psalm-suppress UndefinedFunction */
        @trigger_deprecation('symfonycasts/verify-email-bundle', '1.17.0', '%s() is deprecated and will be removed in v2.0, use validateEmailConfirmationFromRequest() instead.', __METHOD__);

        if (!$this->uriSigner->check($this->signWithoutHost ? self::pathAndQuery($signedUrl) : $signedUrl)) {
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

        if (!$this->checkRequestSignature($request)) {
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

    /**
     * @param array<string, mixed> $extraParams
     */
    private function signWithoutHost(string $routeName, array $extraParams): string
    {
        $uri = $this->router->generate($routeName, $extraParams);

        self::assertUriDoesNotContainAHost($routeName, $uri);

        return $this->toAbsoluteUrl($this->uriSigner->sign($uri));
    }

    /**
     * @param array<string, mixed> $extraParams
     */
    private function signWithHost(string $routeName, array $extraParams): string
    {
        return $this->uriSigner->sign($this->router->generate($routeName, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL));
    }

    private static function assertUriDoesNotContainAHost(string $routeName, string $uri): void
    {
        $host = parse_url($uri, \PHP_URL_HOST);

        if (\is_string($host)) {
            throw new \LogicException(\sprintf('The route "%s" generates a URL with the host "%s" instead of a path, so it cannot be signed while the "sign_without_host" option is enabled. Remove the host or scheme from the route, or set "framework.router.default_uri" to match it.', $routeName, $host));
        }
    }

    private function toAbsoluteUrl(string $pathAndQuery): string
    {
        // An injected UrlHelper would take the host from the current request when there is one, while
        // generate() above only ever uses the router context. Building it here keeps both on the same
        // source, and avoids a BC break on the constructor.
        return (new UrlHelper(new RequestStack(), $this->router))->getAbsoluteUrl($pathAndQuery);
    }

    /**
     * Reduces a URL to what was signed. Callers hand in the URL they received, which is usually
     * absolute, while the signature was made over the path.
     */
    private static function pathAndQuery(string $uri): string
    {
        $path = (string) (parse_url($uri, \PHP_URL_PATH) ?: '');
        $query = parse_url($uri, \PHP_URL_QUERY);

        return \is_string($query) ? $path.'?'.$query : $path;
    }

    /**
     * A signature created without the host has to be checked without it too: UriSigner::checkRequest()
     * always rebuilds the absolute URI from the request.
     */
    private function checkRequestSignature(Request $request): bool
    {
        if (!$this->signWithoutHost) {
            return $this->uriSigner->checkRequest($request);
        }

        $queryString = null !== ($query = $request->getQueryString()) ? '?'.$query : '';

        return $this->uriSigner->check($request->getBaseUrl().$request->getPathInfo().$queryString);
    }
}
