<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

interface ProviderCheckerInterface
{
    /**
     * Returns true if the domain part of the email is supported by the checker
     * This works as a fast check because it doesn't require a DNS query
     */
    public function supportsDomain(string $domainName): bool;

    /**
     * Returns true if the MX servers behind the domain part of the email is supported by the checker
     * This works as a slow check because it requires a DNS query
     *
     * @param string[] $mxServers
     */
    public function supportsMXServers(array $mxServers): bool;

    /**
     * Returns true if the email is valid
     */
    public function check(string $email): bool;
}
