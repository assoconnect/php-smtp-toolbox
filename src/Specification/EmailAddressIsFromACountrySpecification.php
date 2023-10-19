<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

class EmailAddressIsFromACountrySpecification
{
    use EmailIsFromDataTrait;

    public function __construct(private EmailAddressIsFromAProviderSpecification $providerSpecification)
    {
    }

    public function isSatisfiedBy(string $candidate, string $country): bool
    {
        $domain = explode('@', $candidate)[1] ?? null;
        if (null === $domain) {
            return false;
        }

        // First pass without DNS check
        foreach (self::$data as $providerName => $provider) {
            if ($this->providerSpecification->isSatisfiedBy($candidate, $providerName, false)) {
                return $country === $provider['country'];
            }
        }

        // Second pass with DNS check
        foreach (self::$data as $providerName => $provider) {
            if ($this->providerSpecification->isSatisfiedBy($candidate, $providerName, true)) {
                return $country === $provider['country'];
            }
        }

        return false;
    }
}
