<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;

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
        $this->connection->connect('ssl0.ovh.net', 587);
        $this->connection->hello('ovh.net');

        $this->connection->sendCommand('AUTH LOGIN', 'AUTH LOGIN', 334);
        $this->connection->sendCommand('USERNAME', base64_encode($email), 334);
        $this->connection->sendCommand('PASSWORD', base64_encode('hello'), 535);
        // OVH doesn't reply when the address is correct so a short timeout to prevent useless hanging
        $this->connection->setTimeout(3);
        try {
            $this->connection->mail($email);
        } catch (SmtpConnectionRuntimeException $exception) {
            // OVH only replies "Client was not authenticated" when the address is incorrect
            if (false !== strpos($exception->getMessage(), 'Client was not authenticated')) {
                $this->connection->close();
                return false;
            }
        }

        $this->connection->close();
        return true;
    }
}
