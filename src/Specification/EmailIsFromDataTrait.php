<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Specification;

trait EmailIsFromDataTrait
{
    /** @var array<string, array{country: string, domains: string[], mxServers: string[], mxRegex: ?string}> */
    protected static array $data = [
        '1&1' => [
            'country' => 'FR',
            'domains' => [],
            'mxServers' => [
                'mx00.ionos.fr',
            ],
            'mxRegex' => null,
        ],
        'bouygues' => [
            'country' => 'FR',
            'domains' => ['bbox.fr'],
            'mxServers' => [],
            'mxRegex' => null,
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
            'mxRegex' => null,
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
            'mxRegex' => null,
        ],
        'infomaniak' => [
            'country' => 'FR',
            'domains' => [],
            'mxServers' => ['mta-gw.infomaniak.ch'],
            'mxRegex' => null,
        ],
        'laposte' => [
            'country' => 'FR',
            'domains' => ['laposte.net'],
            'mxServers' => [],
            'mxRegex' => null,
        ],
        'microsoft' => [
            'country' => 'US',
            'domains' => [
                'hotmail.fr',
                'hotmail.com',
                'live.fr',
                'outlook.com',
                'outlook.fr',
            ],
            'mxServers' => [],
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
            'mxRegex' => null,
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
            'mxRegex' => null,
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
            'mxServers' => [],
            'mxRegex' => null,
        ],
        'yahoo' => [
            'country' => 'US',
            'domains' => [
                'yahoo.com',
                'yahoo.fr',
            ],
            'mxServers' => [],
            'mxRegex' => null,
        ],
    ];
}
