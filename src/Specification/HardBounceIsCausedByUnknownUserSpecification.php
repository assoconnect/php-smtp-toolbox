<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

class HardBounceIsCausedByUnknownUserSpecification
{
    private const NEEDLES = [
        'Sender user unknown', // SFR
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
