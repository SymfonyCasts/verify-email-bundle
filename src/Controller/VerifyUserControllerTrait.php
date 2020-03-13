<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Controller;

trait VerifyUserControllerTrait
{
    private function getSignature($userId): string
    {
        $expiresAt = (new \DateTimeImmutable('now'))
            ->modify(\sprintf('+%d seconds', $this->helper->getSignatureLifetime()))
        ;

        return $this->helper->generateSignature((string) $userId, $expiresAt)->getSignature();
    }
}
