<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

class BounceIsCausedByUnknownUserSpecification implements BounceCauseSpecificationInterface
{
    private const NEEDLES = [
        // GMail: 5.1.1 The email account that you tried to reach does not exist.
        '5.1.1 The email account that you tried to reach does not exist.',
        // Orange: 5.1.1 <xxx@orange.com>: Recipient address rejected: User unknown
        // Orange: 550 5.1.1 g1aPogOQcUQ7x Adresse d au moins un destinataire invalide. Invalid recipient. OFR_416 [416]
        'Recipient address rejected: User unknown',
        'Adresse d au moins un destinataire invalide. Invalid recipient. OFR_416 [416]',
        // Laposte
        'Adresse destinataire invalide. Invalid recipient',
        // SFR:
        'Sender user unknown',
        // Yahoo: 1 Requested mail action aborted, mailbox not found
        'Requested mail action aborted, mailbox not found',
        '5.1.1 User Unknown',
        'Address unknown',
        '5.1.1 user unknown (UserSearch)',
        'User unknown',
        '5.5.4 Invalid Address',
        'Recipient address rejected: Invalid-Recipient.',
        'Requested action not taken: mailbox unavailable',
        // Postmark default hardBounce message
        'The server was unable to deliver your message (ex: unknown user, mailbox not found).',
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
