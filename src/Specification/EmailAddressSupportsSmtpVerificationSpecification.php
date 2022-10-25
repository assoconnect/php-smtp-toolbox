<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\ProviderChecker\ProviderCheckerInterface;
use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;

class EmailAddressSupportsSmtpVerificationSpecification
{
    /** @var ProviderCheckerInterface[] */
    private iterable $providerCheckers;
    private MxServersResolver $mxServersResolver;

    /**
     * @param ProviderCheckerInterface[] $providerCheckers
     */
    public function __construct(
        iterable $providerCheckers,
        MxServersResolver $mxServersResolver
    ) {
        $this->providerCheckers = $providerCheckers;
        $this->mxServersResolver = $mxServersResolver;
    }

    public function isSatisfiedBy(string $candidate): bool
    {
        $domain = explode('@', $candidate)[1] ?? null;
        if (null === $domain) {
            return false;
        }

        // Early return for excluded domains
        if (in_array($domain, EmailAddressUsesAFrenchMxServerSpecification::NON_FRENCH_DOMAINS, true)) {
            return false;
        }

        // First pass based on the domain
        foreach ($this->providerCheckers as $checker) {
            if ($checker->supportsDomain($domain)) {
                return true;
            }
        }

        // Second pass based on the MX server
        $mxServers = $this->mxServersResolver->getMxServers($domain) ?? [];
        foreach ($this->providerCheckers as $checker) {
            if ($checker->supportsMXServers($mxServers)) {
                return true;
            }
        }

        return false;
    }
}
