<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

class BounceIsCausedByInactiveUserSpecification implements BounceCauseSpecificationInterface
{
    private const NEEDLES = [
        // La Poste: 4.2.1 <xxx@laposte.net>: Recipient address rejected: this mailbox is inactive and has been disabled
        'Recipient address rejected: this mailbox is inactive and has been disabled',
    ];

    public function isSatisfiedBy(string $message): bool
    {
        foreach (self::NEEDLES as $needle) {
            if (false !== strpos($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
