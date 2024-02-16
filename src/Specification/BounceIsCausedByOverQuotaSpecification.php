<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

/** @deprecated see BounceTypeResolver */
class BounceIsCausedByOverQuotaSpecification implements BounceCauseSpecificationInterface
{
    private const NEEDLES = [
        // Free: 552 5.2.2 user quota exceeded (UserSearch)
        // Free: 554 5.2.2 <xxx@free.fr>: Recipient address rejected: Quota exceeded (mailbox for user is full)
        '552 5.2.2 user quota exceeded (UserSearch)',
        'Recipient address rejected: Quota exceeded (mailbox for user is full)',
        // GMail: 4.2.2 The email account that you tried to reach is over quota.
        '4.2.2 The email account that you tried to reach is over quota.',
        // Orange: 552 5.1.1 g270oYR4MmRof Boite du destinataire pleine. Recipient overquota. OFR_417 [417]
        'Recipient overquota',
        // Senat.fr
        'smtp; 452 4.2.2 Over quota',
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
