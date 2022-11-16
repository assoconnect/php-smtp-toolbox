<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;

class EmailAddressUsesAnUSMxServerSpecification
{
    public const DOMAINS = [
        // Google
        'gmail.com',
        // Microsoft
        'hotmail.fr',
        'hotmail.com',
        'live.fr',
        'outlook.com',
        'outlook.fr',
        // Yahoo
        'yahoo.com',
        'yahoo.fr',
        // Single domains
    ];

    public const MX_SERVERS = [
        // Google Workspace
        'aspmx.l.google.com',
        'alt1.aspmx.l.google.com',
        'alt2.aspmx.l.google.com',
        'aspmx2.googlemail.com',
        'aspmx3.googlemail.com',
    ];
    public const MICROSOFT_CUSTOM_MX_SERVER_REGEX = '/.*\.mail\.protection\.outlook\.com/';

    private MxServersResolver $mxServerResolver;

    public function __construct(MxServersResolver $mxServerResolver)
    {
        $this->mxServerResolver = $mxServerResolver;
    }

    public function isSatisfiedBy(string $candidate): bool
    {
        $domain = explode('@', $candidate)[1] ?? null;
        if (null === $domain) {
            return false;
        }

        if (in_array($domain, self::DOMAINS, true)) {
            return true;
        }

        if (in_array($domain, EmailAddressUsesAFrenchMxServerSpecification::DOMAINS, true)) {
            return false;
        }

        $mxServers = $this->mxServerResolver->getMxServers($domain) ?? [];

        return [] !== preg_grep(self::MICROSOFT_CUSTOM_MX_SERVER_REGEX, $mxServers)
            || [] !== array_intersect(self::MX_SERVERS, $mxServers);
    }
}
