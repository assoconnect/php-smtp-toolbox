<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Resolver;

use AssoConnect\SmtpToolbox\Specification\EmailAddressIsFromAProviderSpecification;
use AssoConnect\SmtpToolbox\Specification\EmailIsFromDataTrait;

class ProviderResolver
{
    use EmailIsFromDataTrait;

    public function __construct(
        private EmailAddressIsFromAProviderSpecification $emailAddressIsFromAProviderSpecification
    ) {
    }

    public function resolve(string $address): ?string
    {
        $providers = array_keys(self::$data);

        // First pass without DNS check
        foreach ($providers as $provider) {
            if ($this->emailAddressIsFromAProviderSpecification->isSatisfiedBy($address, $provider, false)) {
                return $provider;
            }
        }

        // Second pass with DNS check
        foreach ($providers as $provider) {
            if ($this->emailAddressIsFromAProviderSpecification->isSatisfiedBy($address, $provider, true)) {
                return $provider;
            }
        }

        return null;
    }
}
