<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Checker;

use AssoConnect\SmtpToolbox\Specification\EmailAddressUsesAFrenchMxServerSpecification;
use AssoConnect\SmtpToolbox\ProviderChecker\ProviderCheckerInterface;
use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;

class SmtpChecker
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

    /**
     * Returns true if the email address is valid
     *         false if the email address is not valid
     *         null if the email address is not verified
     */
    public function check(string $email): ?bool
    {
        $domain = explode('@', $email)[1];

        // Early return for excluded domains
        if (in_array($domain, EmailAddressUsesAFrenchMxServerSpecification::NON_FRENCH_DOMAINS, true)) {
            return null;
        }

        // First pass based on the domain
        foreach ($this->providerCheckers as $checker) {
            if ($checker->supportsDomain($domain)) {
                return $checker->check($email);
            }
        }

        // Second pass based on the MX server
        $mxServers = $this->mxServersResolver->getMxServers($domain) ?? [];
        foreach ($this->providerCheckers as $checker) {
            if ($checker->supportsMXServers($mxServers)) {
                return $checker->check($email);
            }
        }

        return null;
    }
}
