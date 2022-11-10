<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Dto;

class ValidAddressDto implements ValidationStatusDtoInterface
{
    use WithEmailDtoTrait;

    public function __construct(string $email)
    {
        $this->email = $email;
    }
}
