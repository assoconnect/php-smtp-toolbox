<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;

class EmailAddressUsesAFrenchMxServerSpecification
{
    public const DOMAINS = [
        // Free
        'aliceadsl.fr',
        'free.fr',
        // Orange
        'orange.fr',
        'wanadoo.fr',
        // SFR
        '9online.fr',
        'cegetel.net',
        'club.fr',
        'club-internet.fr',
        'estvideo.fr',
        'evc.net',
        'modulonet.fr',
        'neuf.fr',
        'noos.fr',
        'numericable.com',
        'numericable.fr',
        'numericable-caraibes.fr',
        'sfr.fr',
        // Single domains
        'bbox.fr',
        'laposte.net',
    ];

    public const MX_SERVERS = [
        // 1&1
        'mx00.ionos.fr',
        // Free
        'mx1.free.fr',
        'mx2.free.fr',
        // Infomaniak
        'mta-gw.infomaniak.ch',
        // Orange
        'smtp-in.orange.fr',
        'mx.mailbox.orange-business.com',
        // OVH
        'mxb.ovh.net',
        'mx1.ovh.net',
        'mx2.ovh.net',
        'mx3.ovh.net',
        'mx4.ovh.net',
        'mx1.mail.ovh.net',
        'mx2.mail.ovh.net',
        'mx3.mail.ovh.net',
    ];

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

        if (in_array($domain, EmailAddressUsesAnUSMxServerSpecification::DOMAINS, true)) {
            return false;
        }

        $mxServers = $this->mxServerResolver->getMxServers($domain) ?? [];
        return [] !== array_intersect(self::MX_SERVERS, $mxServers);
    }
}
