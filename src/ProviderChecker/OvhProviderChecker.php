<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

/**
 * @link https://docs.ovh.com/fr/emails/generalites-sur-les-emails-mutualises/
 */
class OvhProviderChecker extends AbstractProviderChecker
{
    public const VALID_EXAMPLES = [
        'quebec@vendee.ovh',
    ];

    public const MX_SERVERS = [
        'mxb.ovh.net',
        'mx4.ovh.net',
        'mx3.ovh.net',
        'mx1.mail.ovh.net',
        'mx2.mail.ovh.net',
        'mx3.mail.ovh.net',
    ];

    public function check(string $email): bool
    {
        if (!$this->connection->connect('ssl0.ovh.net', 587)) {
            $this->unsupported();
        }

        if (!$this->connection->hello('ovh.net')) {
            $this->unsupported();
        }
        $this->connection->sendCommand('AUTH LOGIN', 'AUTH LOGIN', 334);
        $this->connection->sendCommand('USERNAME', base64_encode($email), 334);
        $this->connection->sendCommand('PASSWORD', base64_encode('hello'), 335);
        // OVH doesn't reply when the address is correct so a short timeout to prevent useless hanging
        $this->connection->setTimeout(3);
        $this->connection->mail($email);
        $lastReply = $this->connection->getLastReply();

        // OVH only replies "Client was not authenticated" when the address is incorrect
        if (false !== strpos($lastReply, 'Client was not authenticated')) {
            return false;
        }

        return true;
    }
}
