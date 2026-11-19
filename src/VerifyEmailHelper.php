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

    /**
     * @legacy - $signWithoutHost is optional for BC, make it required in 2.0
     */
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
            ? $this->signWithoutHost($routeName, $extraParams, $expiryTimestamp)
            : $this->signWithHost($routeName, $extraParams, $expiryTimestamp);

        /** @psalm-suppress PossiblyFalseArgument */
        return new VerifyEmailSignatureComponents(\DateTimeImmutable::createFromFormat('U', (string) $expiryTimestamp), $signature, $generatedAt);
    }

    public function validateEmailConfirmation(string $signedUrl, string $userId, string $userEmail): void
    {
        /** @psalm-suppress UndefinedFunction */
        @trigger_deprecation('symfonycasts/verify-email-bundle', '1.17.0', '%s() is deprecated and will be removed in v2.0, use validateEmailConfirmationFromRequest() instead.', __METHOD__);

        if (!$this->checkSignature($this->signWithoutHost ? self::pathAndQuery($signedUrl) : $signedUrl)) {
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
    private function signWithoutHost(string $routeName, array $extraParams, int $expiryTimestamp): string
    {
        $uri = $this->router->generate($routeName, $extraParams);

        $this->assertUriDoesNotContainAHost($routeName, $uri);

        return $this->toAbsoluteUrl($this->sign($uri, $expiryTimestamp));
    }

    /**
     * @param array<string, mixed> $extraParams
     */
    private function signWithHost(string $routeName, array $extraParams, int $expiryTimestamp): string
    {
        return $this->sign($this->router->generate($routeName, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL), $expiryTimestamp);
    }

    /**
     * Signs with the expiry computed by generateSignature(), so the expiration the signer enforces is
     * the exact instant the bundle advertises in the "expires" query parameter: giving the signer a
     * duration instead, or a default expiration it would add to its own reading of "now", could make
     * the two expirations drift apart. Signers that predate the argument (the legacy
     * Symfony\Component\HttpKernel\UriSigner, http-foundation < 7.4) ignore it, and keep enforcing
     * "expires" alone.
     */
    private function sign(string $uri, int $expiryTimestamp): string
    {
        return $this->uriSigner->sign($uri, $expiryTimestamp);
    }

    private function assertUriDoesNotContainAHost(string $routeName, string $uri): void
    {
        $host = parse_url($uri, \PHP_URL_HOST);

        if (\is_string($host)) {
            $context = $this->router->getContext();

            throw new \LogicException(\sprintf('The route "%s" generates a URL with the host "%s" instead of a path, because its "host" or "schemes" requirement does not match the router context ("%s://%s"). It cannot be signed while the "sign_without_host" option is enabled: remove the "host" requirement from the route. If the route only requires a scheme, make sure the router context uses it: outside of an HTTP request, set "framework.router.default_uri" (e.g. "https://example.com").', $routeName, $host, $context->getScheme(), $context->getHost()));
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
            return $this->checkSignature($request);
        }

        $queryString = null !== ($query = $request->getQueryString()) ? '?'.$query : '';

        return $this->checkSignature($request->getBaseUrl().$request->getPathInfo().$queryString);
    }

    /**
     * Checks a signature the way the signer wants it checked, keeping an expired link reported as
     * expired: since symfony/http-foundation 7.4, the signer enforces the expiration embedded at
     * signing time itself, and reports an expired signature like a tampered one. When the check
     * fails, verify() is asked which of the two happened. Signers without verify() (the legacy
     * Symfony\Component\HttpKernel\UriSigner, http-foundation < 7.4) never embed an expiration: for
     * those, the expiry is left to the "expires" query parameter, checked by the callers.
     *
     * @param Request|string $uri The signed URI, or the Request it was requested with
     *
     * @throws ExpiredSignatureException When the signature is valid but has expired
     */
    private function checkSignature(Request|string $uri): bool
    {
        $isValid = \is_string($uri) ? $this->uriSigner->check($uri) : $this->uriSigner->checkRequest($uri);

        if ($isValid || !method_exists($this->uriSigner, 'verify')) {
            return $isValid;
        }

        try {
            $this->uriSigner->verify($uri);
        } catch (\Exception $exception) {
            // Matched by name instead of with an instanceof check: the class exists only since
            // symfony/http-foundation 7.4, and this bundle also runs with older versions.
            if ('Symfony\Component\HttpFoundation\Exception\ExpiredSignedUriException' === $exception::class) {
                throw new ExpiredSignatureException();
            }
        }

        // The signature does not verify: check() above already said so.
        return false;
    }
}
