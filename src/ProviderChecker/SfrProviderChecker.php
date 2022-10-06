<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;

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
        $this->connection->connect('smtp.sfr.fr', 587);
        $this->connection->hello('sfr.fr');

        try {
            $this->connection->mail($email);
            $this->connection->close();
            return true;
        } catch (SmtpConnectionRuntimeException $exception) {
            if (false !== strpos($exception->getMessage(), 'Sender user unknown')) {
                $this->connection->close();
                return false;
            }
            throw $exception;
        }
    }
}
