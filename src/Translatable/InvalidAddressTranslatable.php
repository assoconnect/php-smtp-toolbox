<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Translatable;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvalidAddressTranslatable implements TranslatableInterface
{
    private string $reason;
    private string $email;
    private string $domain;
    private string $smtpResponse;

    public function __construct(string $reason, string $email, string $smtpResponse)
    {
        $this->reason = $reason;
        $this->email = $email;
        $this->domain = explode('@', $email)[1] ?? '';
        $this->smtpResponse = $smtpResponse;
    }

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans(
            'invalid_address.' . $this->reason,
            [
                'domain' => $this->domain,
                'email' => $this->email,
                'smtpResponse' => $this->smtpResponse,
            ],
            'assoconnect_smtp_toolbox'
        );
    }
}
