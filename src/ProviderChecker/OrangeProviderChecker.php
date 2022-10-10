<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;

/**
 * @link https://aide.laposte.net/contents/comment-parametrer-un-logiciel-de-messagerie-pour-envoyer-et-recevoir-mes-courriers-electroniques
 */
class OrangeProviderChecker extends AbstractProviderChecker
{
    public const VALID_EXAMPLES = [
        'mme-petit-christelle82@orange.fr',
    ];

    public const DOMAINS = [
        'orange.fr',
    ];

    public const MX_SERVERS = [
        'smtp-in.orange.fr',
    ];

    public function check(string $email): bool
    {
        $this->connection->connect('smtp-in.orange.fr', 25);
        $this->connection->hello('orange.fr');
        $this->connection->mail($email);
        try {
            $this->connection->recipient($email);
            return true;
        } catch (SmtpConnectionRuntimeException $exception) {
            // 550 5.1.1 hnbpoTFYusAjD Adresse d au moins un destinataire invalide. Invalid recipient. OFR_416 [416]
            if (false !== strpos($exception->getMessage(), 'Adresse d au moins un destinataire invalide')) {
                return false;
            }

            throw $exception;
        }
    }
}
