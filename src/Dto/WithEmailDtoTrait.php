<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Dto;

trait WithEmailDtoTrait
{
    private string $email;

    public function getEmail(): string
    {
        return $this->email;
    }
}
