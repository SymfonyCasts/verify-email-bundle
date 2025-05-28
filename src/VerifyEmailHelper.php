<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SymfonyCasts\Bundle\VerifyEmail;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailRuntimeException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\WrongEmailVerifyException;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final readonly class VerifyEmailHelper implements VerifyEmailHelperInterface
{
    /**
     * @param int $lifetime The length of time in seconds that a signed URI is valid for after it is created
     */
    public function __construct(
        private UrlGeneratorInterface $router,
        private UriSigner $uriSigner,
        private VerifyEmailTokenGenerator $tokenGenerator,
        private int $lifetime,
    ) {
    }

    /**
     * @throws VerifyEmailRuntimeException
     */
    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParams = []): VerifyEmailSignatureComponents
    {
        $generatedAt = time();
        $expiryTimestamp = $generatedAt + $this->lifetime;

        $extraParams['token'] = $this->tokenGenerator->createToken($userId, $userEmail);
        $extraParams['expires'] = $expiryTimestamp;

        $uri = $this->router->generate($routeName, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL);

        $signature = $this->uriSigner->sign($uri);

        if (!$expiresAt = \DateTimeImmutable::createFromFormat('U', (string) $expiryTimestamp)) {
            throw new VerifyEmailRuntimeException(\sprintf('Unable to create DateTimeImmutable from timestamp: %s', $expiryTimestamp));
        }

        return new VerifyEmailSignatureComponents($expiresAt, $signature, $generatedAt);
    }

    public function validateEmailConfirmationFromRequest(Request $request, string $userId, string $userEmail): void
    {
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
