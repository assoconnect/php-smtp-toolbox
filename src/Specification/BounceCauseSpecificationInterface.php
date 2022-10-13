<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

interface BounceCauseSpecificationInterface
{
    public function isSatisfiedBy(string $message): bool;
}
