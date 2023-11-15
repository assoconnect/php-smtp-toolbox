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

    public function isSatisfiedBy(string $candidate, string $providerName, bool $checkDns): bool
    {
        $domain = explode('@', $candidate)[1] ?? null;
        if (null === $domain) {
            return false;
        }

        if (!isset(self::$data[$providerName])) {
            return false;
        }
        $provider = self::$data[$providerName];

        // First pass without DNS check
        if (in_array($domain, $provider['domains'], true)) {
            return true;
        }

        if (isset($provider['domainsWithoutTLD'])) {
            $domainWithoutTLD = implode('.', array_slice(explode('.', $domain), 0, -1));
            if (in_array($domainWithoutTLD, $provider['domainsWithoutTLD'], true)) {
                return true;
            }
        }

        // Second pass with DNS check
        if ($checkDns) {
            $mxServers = $this->mxServerResolver->getMxServers($domain) ?? [];
            if (isset($provider['mxServers']) && [] !== array_intersect($provider['mxServers'], $mxServers)) {
                return true;
            }

            if (isset($provider['mxRegex']) && [] !== preg_grep($provider['mxRegex'], $mxServers)) {
                return true;
            }
        }

        return false;
    }
}
