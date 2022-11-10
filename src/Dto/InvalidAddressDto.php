<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Dto;

use AssoConnect\SmtpToolbox\Translatable\InvalidAddressTranslatable;

class InvalidAddressDto implements ValidationStatusDtoInterface
{
    use WithEmailDtoTrait;

    private string $reason;
    private ?string $smtpResponse;

    private function __construct(string $email, string $reason, string $smtpResponse = null)
    {
        $this->email = $email;
        $this->reason = $reason;
        $this->smtpResponse = $smtpResponse;
    }

    public static function noAtSymbol(string $email): self
    {
        return new self($email, 'no_at_symbol');
    }

    public static function noMxServers(string $email): self
    {
        return new self($email, 'no_mx_servers');
    }

    public static function unknownUser(string $email, string $smtpResponse): self
    {
        return new self($email, 'unknown_user', $smtpResponse);
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getLocalizedReason(): InvalidAddressTranslatable
    {
        return new InvalidAddressTranslatable(
            $this->reason,
            $this->email,
            $this->smtpResponse ?? ''
        );
    }

    public function getSmtpResponse(): ?string
    {
        return $this->smtpResponse;
    }
}
