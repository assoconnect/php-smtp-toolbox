<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

/**
 * @phpstan-type DomainData array{
 *   country: string,
 *   domains: string[],
 *   domainsWithoutTLD?: string[],
 *   mxServers?: string[],
 *   mxRegex?: string
 * }
 */
trait EmailIsFromDataTrait
{
    /** @var array<string, DomainData> */
    protected static array $data = [
        '1&1' => [
            'country' => 'FR',
            'domains' => [],
            'mxServers' => [
                'mx00.ionos.fr',
            ],
        ],
        'apple' => [
            'country' => 'US',
            'domains' => [
                'icloud.com',
                'mac.com',
                'me.com',
            ],
        ],
        'bouygues' => [
            'country' => 'FR',
            'domains' => ['bbox.fr'],
        ],
        'free' => [
            'country' => 'FR',
            'domains' => [
                'aliceadsl.fr',
                'free.fr',
            ],
            'mxServers' => [
                'mx1.free.fr',
                'mx2.free.fr',
            ],
        ],
        'google' => [
            'country' => 'US',
            'domains' => ['gmail.com'],
            'mxServers' => [
                'aspmx.l.google.com',
                'alt1.aspmx.l.google.com',
                'alt2.aspmx.l.google.com',
                'aspmx2.googlemail.com',
                'aspmx3.googlemail.com',
            ],
        ],
        'infomaniak' => [
            'country' => 'FR',
            'domains' => [],
            'mxServers' => ['mta-gw.infomaniak.ch'],
        ],
        'laposte' => [
            'country' => 'FR',
            'domains' => ['laposte.net'],
        ],
        'microsoft' => [
            'country' => 'US',
            'domains' => [
                'live.fr',
            ],
            'domainsWithoutTLD' => [
                'hotmail',
                'outlook',
            ],
            'mxRegex' => '/.*\.mail\.protection\.outlook\.com/',
        ],
        'orange' => [
            'country' => 'FR',
            'domains' => [
                'orange.fr',
                'wanadoo.fr',
            ],
            'mxServers' => [
                'smtp-in.orange.fr',
                'mx.mailbox.orange-business.com',
            ],
        ],
        'ovh' => [
            'country' => 'FR',
            'domains' => [],
            'mxServers' => [
                'mxb.ovh.net',
                'mx1.ovh.net',
                'mx2.ovh.net',
                'mx3.ovh.net',
                'mx4.ovh.net',
                'mx1.mail.ovh.net',
                'mx2.mail.ovh.net',
                'mx3.mail.ovh.net',
            ],
        ],
        'sfr' => [
            'country' => 'FR',
            'domains' => [
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
            ],
        ],
        'yahoo' => [
            'country' => 'US',
            'domains' => [
                'yahoo.co.uk',
            ],
            'domainsWithoutTLD' => [
                'yahoo',
            ],
        ],
    ];
}
