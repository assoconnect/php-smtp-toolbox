<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\ProviderClient;

use AssoConnect\SmtpToolbox\ProviderClient\GenericProviderClient;
use AssoConnect\SmtpToolbox\Resolver\BounceTypeResolver;
use AssoConnect\SmtpToolbox\Specification\BounceIsCausedByInactiveUserSpecification;
use AssoConnect\SmtpToolbox\Specification\BounceIsCausedByUnknownUserSpecification;
use AssoConnect\SmtpToolbox\Specification\ExceptionComesFromTemporaryFailureSpecification;
use PHPMailer\PHPMailer\SMTP;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class GenericProviderClientTest extends TestCase
{
    public function testConnectionUsesTheDefaultTimeout(): void
    {
        self::assertSame(
            GenericProviderClient::DEFAULT_CONNECT_TIMEOUT,
            $this->connectTimeoutUsedBy(null),
        );
    }

    public function testConnectionUsesTheConfiguredTimeout(): void
    {
        self::assertSame(5, $this->connectTimeoutUsedBy(5));
    }

    private function connectTimeoutUsedBy(?int $connectTimeout): ?int
    {
        $connection = new class () extends SMTP {
            private ?int $timeout = null;

            /**
             * @param string $host
             * @param int|null $port
             * @param int $timeout
             * @param array<mixed> $options
             */
            public function connect($host, $port = null, $timeout = 30, $options = []): bool
            {
                $this->timeout = $timeout;

                return false;
            }

            public function getConnectTimeout(): ?int
            {
                return $this->timeout;
            }
        };

        $client = new class ($connection, $connectTimeout) extends GenericProviderClient {
            public function __construct(private readonly SMTP $connection, ?int $connectTimeout)
            {
                $logger = new NullLogger();
                $temporaryFailure = new ExceptionComesFromTemporaryFailureSpecification();
                $unknownUser = new BounceIsCausedByUnknownUserSpecification();
                $inactiveUser = new BounceIsCausedByInactiveUserSpecification();
                $bounceTypeResolver = new BounceTypeResolver();

                if (null === $connectTimeout) {
                    parent::__construct(
                        $logger,
                        $temporaryFailure,
                        $unknownUser,
                        $inactiveUser,
                        $bounceTypeResolver,
                        'hello.org',
                    );
                } else {
                    parent::__construct(
                        $logger,
                        $temporaryFailure,
                        $unknownUser,
                        $inactiveUser,
                        $bounceTypeResolver,
                        'hello.org',
                        $connectTimeout,
                    );
                }
            }

            protected function createConnection(): SMTP
            {
                return $this->connection;
            }
        };

        try {
            $client->check('john@example.org', 'mx.example.org');
        } catch (\Exception) {
            // A refused connection always ends in an exception; only the timeout passed to connect() matters here
        }

        return $connection->getConnectTimeout();
    }
}
