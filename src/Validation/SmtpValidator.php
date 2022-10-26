<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Validation;

use AssoConnect\SmtpToolbox\Dto\InvalidAddressDto;
use AssoConnect\SmtpToolbox\Dto\UnverifiedAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;
use AssoConnect\SmtpToolbox\ProviderClient\CustomProviderClientInterface;
use AssoConnect\SmtpToolbox\ProviderClient\GenericProviderClient;
use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;
use Psr\Log\LoggerInterface;

class SmtpValidator
{
    private MxServersResolver $mxServersResolver;
    private GenericProviderClient $genericProviderClient;
    private LoggerInterface $logger;

    public function __construct(
        MxServersResolver $mxServersResolver,
        GenericProviderClient $genericProviderClient,
        LoggerInterface $logger
    ) {
        $this->mxServersResolver = $mxServersResolver;
        $this->genericProviderClient = $genericProviderClient;
        $this->logger = $logger;
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
            try {
                return $this->genericProviderClient->check($email, $mxServer);
            } catch (SmtpConnectionRuntimeException $exception) {
                $this->logger->debug(sprintf(
                    '%s - %s responded: %s',
                    $email,
                    $mxServer,
                    $exception->getMessage()
                ));
            }
        }

        return new UnverifiedAddressDto($email);
    }
}
