<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;

class ExceptionComesFromTemporaryFailureSpecification
{
    public function isSatisfiedBy(SmtpConnectionRuntimeException $exception): bool
    {
        return (int)floor($exception->getCode() / 100) === 4 // 4zy codes describe temporary failures
            || 1 === preg_match('/Connection timed out/', $exception->getMessage());
    }
}
