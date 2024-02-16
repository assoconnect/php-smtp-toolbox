<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

/** @deprecated see BounceTypeResolver */
class BounceIsCausedByInactiveUserSpecification implements BounceCauseSpecificationInterface
{
    private const NEEDLES = [
        // La Poste: 4.2.1 <xxx@laposte.net>: Recipient address rejected: this mailbox is inactive and has been disabled
        'Recipient address rejected: this mailbox is inactive and has been disabled',
        'recipient address rejected: Inactive',
        'The email account that you tried to reach is over quota and inactive.',
        'This mailbox is disabled',
    ];

    public function isSatisfiedBy(string $message): bool
    {
        foreach (self::NEEDLES as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
