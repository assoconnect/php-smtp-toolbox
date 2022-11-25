<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderClient;

use AssoConnect\SmtpToolbox\Dto\InvalidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;
use AssoConnect\SmtpToolbox\Exception\SmtpTemporaryFailureException;
use AssoConnect\SmtpToolbox\Specification\ExceptionComesFromTemporaryFailureSpecification;
use PHPMailer\PHPMailer\SMTP;
use Psr\Log\LoggerInterface;

class GenericProviderClient
{
    private SMTP $connection;

    /**
     * Host domain to use to connect to the MX servers
     * Warning: some MX servers require the domain to point to an IP with a valid reverse DNS record
     */
    private string $host;
    private ExceptionComesFromTemporaryFailureSpecification $exceptionComesFromTemporaryFailureSpecification;
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        ExceptionComesFromTemporaryFailureSpecification $exceptionComesFromTemporaryFailureSpecification,
        string $host
    ) {
        $this->logger = $logger;
        $this->connection = new SMTP();
        $this->connection->Debugoutput = $logger;
        $this->exceptionComesFromTemporaryFailureSpecification = $exceptionComesFromTemporaryFailureSpecification;
        $this->host = $host;
    }

    /**
     * @throws SmtpTemporaryFailureException
     */
    public function check(string $email, string $mxServer): ValidationStatusDtoInterface
    {
        try {
            // Based on https://github.com/PHPMailer/PHPMailer/blob/master/examples/smtp_check.phps
            if (!$this->connection->connect($mxServer)) {
                throw new SmtpConnectionRuntimeException(
                    sprintf('Failed to connect to server: %s', $mxServer)
                );
            }
            if (!$this->connection->hello($this->host)) {
                throw SmtpConnectionRuntimeException::createFromSmtpError(
                    'EHLO',
                    $this->connection->getError()
                );
            }
            $extensionList = $this->connection->getServerExtList();
            if (is_array($extensionList) && array_key_exists('STARTTLS', $extensionList)) {
                if (!$this->connection->startTLS()) {
                    throw SmtpConnectionRuntimeException::createFromSmtpError(
                        'STARTTLS',
                        $this->connection->getError()
                    );
                }
                // Repeat EHLO after STARTTLS
                if (!$this->connection->hello($mxServer)) {
                    throw SmtpConnectionRuntimeException::createFromSmtpError(
                        'EHLO (2)',
                        $this->connection->getError()
                    );
                }
            }
            if (!$this->connection->mail('john@' . $this->host)) {
                throw SmtpConnectionRuntimeException::createFromSmtpError(
                    "MAIL FROM",
                    $this->connection->getError()
                );
            }
            if (!$this->connection->recipient($email)) {
                throw SmtpConnectionRuntimeException::createFromSmtpError(
                    'RCPT TO',
                    $this->connection->getError()
                );
            }
            $this->connection->quit();

            return new ValidAddressDto($email);
        } catch (SmtpConnectionRuntimeException $exception) {
            $this->logger->debug(
                sprintf(
                    '%s - %s responded: %s (%d)',
                    $email,
                    $mxServer,
                    $exception->getMessage(),
                    $exception->getCode()
                )
            );
            $response = $this->connection->getLastReply();
            $this->connection->quit();

            if (550 === $exception->getCode()) {
                return InvalidAddressDto::unknownUser($email, $response);
            }
            if (552 === $exception->getCode()) {
                return new ValidAddressDto($email);
            }
            if ($this->exceptionComesFromTemporaryFailureSpecification->isSatisfiedBy($exception)) {
                throw new SmtpTemporaryFailureException($exception->getMessage(), $exception->getCode(), $exception);
            }

            throw $exception;
        }
    }
}
