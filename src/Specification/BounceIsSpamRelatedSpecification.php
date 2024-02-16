<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\Resolver\BounceTypeResolver;

class BounceIsSpamRelatedSpecification implements BounceCauseSpecificationInterface
{
    private function __construct(private readonly BounceTypeResolver $bounceTypeResolver)
    {
    }

    public function isSatisfiedBy(string $message): bool
    {
        $bounceType = $this->bounceTypeResolver->resolve($message);

        return BounceTypeResolver::BOUNCE_REASON_SPAMMY === $bounceType;
    }
}
