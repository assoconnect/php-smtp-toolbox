<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;

class EmailAddressIsFromAProviderSpecification
{
    use EmailIsFromDataTrait;

    public function __construct(private MxServersResolver $mxServerResolver)
    {
    }

    public function isSatisfiedBy(string $candidate, string $providerName, bool $checkDns = true): bool
    {
        $domain = explode('@', $candidate)[1] ?? null;
        if (null === $domain) {
            return false;
        }

        $provider = self::$data[$providerName];

        // First pass without DNS check
        if (in_array($domain, $provider['domains'], true)) {
            return true;
        }

        // Second pass with DNS check
        if ($checkDns) {
            $mxServers = $this->mxServerResolver->getMxServers($domain) ?? [];
            if ([] !== array_intersect($provider['mxServers'], $mxServers)) {
                return true;
            }

            if (null !== $provider['mxRegex'] && [] !== preg_grep($provider['mxRegex'], $mxServers)) {
                return true;
            }
        }

        return false;
    }
}
