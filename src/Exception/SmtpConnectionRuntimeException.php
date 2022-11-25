<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Exception;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use PHPMailer\PHPMailer\SMTP;

class SmtpConnectionRuntimeException extends \RuntimeException
{
    private string $lastReply;

    public function __construct(string $message, int $code, string $lastReply)
    {
        $this->lastReply = $lastReply;
        parent::__construct($message, $code);
    }

    public function getLastReply(): string
    {
        return $this->lastReply;
    }

    public static function createFromSmtpError(string $command, SMTP $smtp): self
    {
        $error = $smtp->getError();
        return new self(
            sprintf(
                '%s failed: %s (%s - %s), "%s"',
                $command,
                $error['error'],
                $error['smtp_code'],
                $error['smtp_code_ex'],
                $error['detail']
            ),
            (int) $error['smtp_code'],
            $smtp->getLastReply()
        );
    }
}
