<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

class BounceReasonIsUnknownSpecification implements BounceCauseSpecificationInterface
{
    private const NEEDLES = [
        // Sendinblue
        'hard bounce',
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
