<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;

/**
 * @link https://aide.laposte.net/contents/comment-parametrer-un-logiciel-de-messagerie-pour-envoyer-et-recevoir-mes-courriers-electroniques
 */
class LaposteProviderChecker extends AbstractProviderChecker
{
    public const VALID_EXAMPLES = [
        'francine.lm@laposte.net',
    ];

    public const DOMAINS = [
        'laposte.net',
    ];

    public function check(string $email): bool
    {
        $this->connection->connect('smtp.laposte.net', 587);
        $this->connection->hello('laposte.net');

        try {
            $this->connection->mail($email);
        } catch (SmtpConnectionRuntimeException $exception) {
            // Authentification requise. Veuillez verifier la configuration de votre logiciel de messagerie. LPN105_402
            if (false !== strpos($exception->getMessage(), 'LPN105_402')) {
                $this->connection->close();
                return true;
            }

            $invalidEmail = sprintf(' <%s>: Sender address rejected: Access denied', $email);
            if (false !== strpos($exception->getMessage(), $invalidEmail)) {
                $this->connection->close();
                return false;
            }

            throw $exception;
        }

        $this->unsupported();
    }
}
