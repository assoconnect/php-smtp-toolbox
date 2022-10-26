<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderClient;

use AssoConnect\SmtpToolbox\Connection\SmtpConnection;
use AssoConnect\SmtpToolbox\Dto\InvalidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;

class GenericProviderClient
{
    private SmtpConnection $connection;

    /**
     * Host domain to use to connect to the MX servers
     * Warning: some MX servers require the domain to point to an IP with a valid reverse DNS record
     */
    private string $host;

    public function __construct(
        SmtpConnection $connection,
        string $host
    ) {
        $this->connection = $connection;
        $this->host = $host;
    }

    public function check(string $email, string $mxServer): ValidationStatusDtoInterface
    {
        $this->connection->connect($mxServer);
        $this->connection->hello($this->host);
        $this->connection->mail('john@' . $this->host);

        try {
            $this->connection->recipient($email);
            $this->connection->quit();

            return new ValidAddressDto($email);
        } catch (SmtpConnectionRuntimeException $exception) {
            $response = $this->connection->getLastReply();
            $this->connection->quit();

            if (550 === $exception->getCode()) {
                return InvalidAddressDto::unknownUser($email, $response);
            }

            throw $exception;
        }
    }
}
