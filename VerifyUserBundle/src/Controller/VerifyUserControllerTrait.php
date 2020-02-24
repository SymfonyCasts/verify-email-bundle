<?php

declare(strict_types=1);

namespace SymfonyCasts\Bundle\VerifyUser\Controller;

trait VerifyUserControllerTrait
{
    private function getSignature($userId): string
    {
        $expiresAt = (new \DateTimeImmutable('now'))
            ->modify(sprintf('+%d seconds', $this->helper->getSignatureLifetime()))
        ;

        return $this->helper->generateSignature((string) $userId, $expiresAt)->getSignature();
    }
}
