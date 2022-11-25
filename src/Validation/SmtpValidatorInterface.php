<?php

namespace AssoConnect\SmtpToolbox\Validation;

use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;
use AssoConnect\SmtpToolbox\Exception\SmtpTemporaryFailureException;

interface SmtpValidatorInterface
{
    /** @throws SmtpTemporaryFailureException|SmtpConnectionRuntimeException */
    public function validate(string $email): ValidationStatusDtoInterface;
}
