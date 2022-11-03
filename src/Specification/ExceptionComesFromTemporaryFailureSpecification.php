<?php

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;

class ExceptionComesFromTemporaryFailureSpecification
{
    public function isSatisfiedBy(SmtpConnectionRuntimeException $exception): boolean
    {
        return (int)floor($exception->getCode() / 100) === 4 // 4zy codes describe temporary failures
            || preg_match('/Connection timed out/', $exception->getMessage());
    }
}