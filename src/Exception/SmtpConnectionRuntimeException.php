<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Exception;

class SmtpConnectionRuntimeException extends \RuntimeException
{
    /** @param array<string, string> $smtpError */
    public static function createFromSmtpError(string $command, array $smtpError): self
    {
        return new self(
            sprintf(
                '%s failed: %s (%s - %s)',
                $command,
                $smtpError['error'],
                $smtpError['smtp_code'],
                $smtpError['smtp_code_ex']
            ),
            (int)$smtpError['smtp_code']
        );
    }
}
