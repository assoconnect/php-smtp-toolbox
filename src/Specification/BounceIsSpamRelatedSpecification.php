<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

class BounceIsSpamRelatedSpecification implements BounceCauseSpecificationInterface
{
    private const NEEDLES = [
        'spam detected',
        'rejected per SPAM policy',
        'Not delivering to a user who marked your messages as spam'
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
