<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

class BounceIsSpamRelatedSpecification implements BounceCauseSpecificationInterface
{
    public function isSatisfiedBy(string $message): bool
    {
        return false !== strpos(strtolower($message), 'spam');
    }
}
