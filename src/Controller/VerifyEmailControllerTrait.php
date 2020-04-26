<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides useful methods to a "verify email controller".
 *
 * Use of this trait requires a controller to extend
 * Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 *
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
class VerifyEmailControllerTrait
{
    private function isConfirmationValid(Request $request): void
    {
        $user = $this->getUser();

        if ($user->isVerified()) {
            throw new \Exception('Your email address has already been verified.');
        }

        if (!$this->helper->isValidSignature($request->getRequestUri(), $user->getId(), $user->getEmail())) {
            throw new \Exception('Invalid confirmation signature.');
        }
    }

    private function markUserAsVerified(): void
    {
        $user = $this->getUser();
        $user->setVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
