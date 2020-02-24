<?php

declare(strict_types=1);

namespace SymfonyCasts\Bundle\VerifyUser\Controller;

trait VerifyUserControllerTrait
{
    private function getSignature($userId): string
    {
        //@TODO arg value from helper->getLifetime()
        $expiresAt = (new \DateTimeImmutable('now'))
            ->modify(sprintf('+%d seconds', 3600))
        ;

        return $this->helper->generateSignature($userId, $expiresAt)->getSignature();
    }
}
