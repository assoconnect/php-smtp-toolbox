<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderChecker;

use AssoConnect\SmtpToolbox\Connection\SmtpConnection;
use AssoConnect\SmtpToolbox\Dto\SmtpCheckResultDto;
use AssoConnect\SmtpToolbox\Exception\UnsupportedSmtpResponseException;

abstract class AbstractProviderChecker implements ProviderCheckerInterface
{
    /**
     * List of valid email addresses for this provider
     * @var string[]
     */
    public const VALID_EXAMPLES = [];

    /**
     * List of domains supported by this provider
     * @var string[]
     */
    public const DOMAINS = [];

    /**
     * List of MX servers supported by this provider
     * @var string[]
     */
    public const MX_SERVERS = [];

    protected SmtpConnection $connection;

    public function __construct(SmtpConnection $connection)
    {
        $this->connection = $connection;
    }

    public function supportsDomain(string $domainName): bool
    {
        return in_array($domainName, static::DOMAINS, true);
    }

    public function supportsMXServers(array $mxServers): bool
    {
        return [] !== array_intersect(static::MX_SERVERS, $mxServers);
    }

    /** @return never */
    protected function unsupported(): void
    {
        $lastReply = $this->connection->getLastReply();
        $this->connection->quit();
        throw new UnsupportedSmtpResponseException($lastReply);
    }
}
