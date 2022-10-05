<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\ProviderChecker\LaposteProviderChecker;
use AssoConnect\SmtpToolbox\ProviderChecker\OvhProviderChecker;
use AssoConnect\SmtpToolbox\ProviderChecker\SfrProviderChecker;
use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;

class EmailAddressUsesAFrenchMxServerSpecification
{
    public const FRENCH_DOMAINS = [
        // Free
        'aliceadsl.fr',
        'free.fr',
        // Orange
        'orange.fr',
        'wanadoo.fr',
        // Single domains
        'bbox.fr',
    ];

    public const NON_FRENCH_DOMAINS = [
        // Google
        'gmail.com',
        // Microsoft
        'hotmail.fr',
        'hotmail.com',
        'live.fr',
        'outlook.com',
        'outlook.fr',
        // Single domains
        'yahoo.com',
        'yahoo.fr',
        // Custom domains
        'assoconnect.com', // Google
        'epgv.fr', // Microsoft
        'etikl.com', // Google
        'mot.asso.fr' // Microsoft
    ];
    public const FRENCH_MX_SERVERS = [
        // 1&1
        'mx00.ionos.fr',
        // Free
        'mx1.free.fr',
        'mx2.free.fr',
        // Infomaniak
        'mta-gw.infomaniak.ch',
        // Orange
        'mx.mailbox.orange-business.com',
    ];

    private MxServersResolver $mxServerResolver;

    public function __construct(MxServersResolver $mxServerResolver)
    {
        $this->mxServerResolver = $mxServerResolver;
    }

    public function isSatisfiedBy(string $candidate): bool
    {
        $domain = explode('@', $candidate)[1];

        $frenchDomains = array_merge(
            self::FRENCH_DOMAINS,
            LaposteProviderChecker::DOMAINS,
            SfrProviderChecker::DOMAINS,
        );
        if (in_array($domain, $frenchDomains, true)) {
            return true;
        }

        if (in_array($domain, self::NON_FRENCH_DOMAINS, true)) {
            return false;
        }

        $mxServers = $this->mxServerResolver->getMxServers($domain) ?? [];
        $frenchMxServers = array_merge(
            self::FRENCH_MX_SERVERS,
            OvhProviderChecker::MX_SERVERS,
        );
        return [] !== array_intersect($frenchMxServers, $mxServers);
    }
}
