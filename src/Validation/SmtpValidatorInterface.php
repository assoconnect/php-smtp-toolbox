<?php

namespace AssoConnect\SmtpToolbox\Validation;

use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\Exception\SmtpTemporaryFailureException;

interface SmtpValidatorInterface
{
    /** @throws SmtpTemporaryFailureException */
    public function validate(string $email): ValidationStatusDtoInterface;
}