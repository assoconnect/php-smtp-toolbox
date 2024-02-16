<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

/** @deprecated see BounceTypeResolver */
class BounceReasonIsUnknownSpecification implements BounceCauseSpecificationInterface
{
    private const NEEDLES = [
        // Sendinblue
        'hard bounce',
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
