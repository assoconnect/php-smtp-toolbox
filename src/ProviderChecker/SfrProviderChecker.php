<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

/**
 * @link https://assistance.sfr.fr/sfrmail-appli/sfrmail/configurer-messagerie-recevoir-email-sfr.html
 */
class SfrProviderChecker extends AbstractProviderChecker
{
    public const VALID_EXAMPLES = [
        'chantal.blanc@noos.fr',
        'charue.am@numericable.fr',
        'marc.croizer@sfr.fr',
    ];

    public const DOMAINS = [
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
    ];

    public function check(string $email): bool
    {
        if (!$this->connection->connect('smtp.sfr.fr', 587)) {
            $this->unsupported();
        }

        if (true !== $this->connection->hello('sfr.fr')) {
            $this->unsupported();
        }

        if ($this->connection->mail($email)) {
            return true;
        }

        if (false !== strpos($this->connection->getLastReply(), 'Sender user unknown')) {
            return false;
        }

        $this->unsupported();
    }
}
