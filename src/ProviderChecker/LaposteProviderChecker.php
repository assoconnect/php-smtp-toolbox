<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

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

    public function supportsDomain(string $domainName): bool
    {
        return in_array($domainName, self::DOMAINS, true);
    }

    public function check(string $email): bool
    {
        if (!$this->connection->connect('smtp.laposte.net', 587)) {
            $this->unsupported();
        }

        if (!$this->connection->hello('laposte.net')) {
            $this->unsupported();
        }
        $this->connection->mail($email);
        $lastReply = $this->connection->getLastReply();

        // Authentification requise. Veuillez verifier la configuration de votre logiciel de messagerie. LPN105_402
        if (false !== strpos($lastReply, 'LPN105_402')) {
            return true;
        }

        $invalidEmail = sprintf(' <%s>: Sender address rejected: Access denied', $email);
        if (false !== strpos($lastReply, $invalidEmail)) {
            return false;
        }

        $this->unsupported();
    }
}
