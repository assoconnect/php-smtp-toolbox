<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Validation;

use AssoConnect\SmtpToolbox\Dto\InvalidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\ProviderClient\GenericProviderClient;
use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;

class SmtpValidator implements SmtpValidatorInterface
{
    private MxServersResolver $mxServersResolver;
    private GenericProviderClient $genericProviderClient;

    public function __construct(
        MxServersResolver $mxServersResolver,
        GenericProviderClient $genericProviderClient
    ) {
        $this->mxServersResolver = $mxServersResolver;
        $this->genericProviderClient = $genericProviderClient;
    }

    public function validate(string $email): ValidationStatusDtoInterface
    {
        $parts = explode('@', $email);
        if (!isset($parts[1])) {
            return InvalidAddressDto::noAtSymbol($email);
        }

        $domain = $parts[1];

        $mxServers = $this->mxServersResolver->getMxServers($domain);

        if ([] === $mxServers || null === $mxServers) {
            return InvalidAddressDto::noMxServers($email);
        }

        foreach ($mxServers as $mxServer) {
            return $this->genericProviderClient->check($email, $mxServer);
        }
    }
}
