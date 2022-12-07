<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\ProviderClient;

use AssoConnect\SmtpToolbox\Dto\InvalidAddressDto;
use AssoConnect\SmtpToolbox\Dto\UnverifiedAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;
use AssoConnect\SmtpToolbox\Exception\SmtpTemporaryFailureException;
use AssoConnect\SmtpToolbox\Specification\BounceIsCausedByInactiveUserSpecification;
use AssoConnect\SmtpToolbox\Specification\BounceIsCausedByUnknownUserSpecification;
use AssoConnect\SmtpToolbox\Specification\ExceptionComesFromTemporaryFailureSpecification;
use PHPMailer\PHPMailer\SMTP;
use Psr\Log\LoggerInterface;

class GenericProviderClient
{
    /**
     * Host domain to use to connect to the MX servers
     * Warning: some MX servers require the domain to point to an IP with a valid reverse DNS record
     */
    private string $host;
    private ExceptionComesFromTemporaryFailureSpecification $exceptionComesFromTemporaryFailureSpecification;
    private LoggerInterface $logger;
    private BounceIsCausedByUnknownUserSpecification $bounceIsCausedByUnknownUserSpecification;
    private BounceIsCausedByInactiveUserSpecification $bounceIsCausedByInactiveUserSpecification;

    public function __construct(
        LoggerInterface $logger,
        ExceptionComesFromTemporaryFailureSpecification $exceptionComesFromTemporaryFailureSpecification,
        BounceIsCausedByUnknownUserSpecification $bounceIsCausedByUnknownUserSpecification,
        BounceIsCausedByInactiveUserSpecification $bounceIsCausedByInactiveUserSpecification,
        string $host
    ) {
        $this->logger = $logger;
        $this->exceptionComesFromTemporaryFailureSpecification = $exceptionComesFromTemporaryFailureSpecification;
        $this->bounceIsCausedByUnknownUserSpecification = $bounceIsCausedByUnknownUserSpecification;
        $this->bounceIsCausedByInactiveUserSpecification = $bounceIsCausedByInactiveUserSpecification;
        $this->host = $host;
    }

    /**
     * @throws SmtpTemporaryFailureException
     */
    public function check(string $email, string $mxServer): ValidationStatusDtoInterface
    {
        $connection = new SMTP();
        $connection->Debugoutput = $this->logger;
        try {
            // Based on https://github.com/PHPMailer/PHPMailer/blob/master/examples/smtp_check.phps
            if (!$connection->connect($mxServer)) {
                throw new SmtpConnectionRuntimeException(
                    sprintf('Failed to connect to server: %s', $mxServer),
                    0,
                    $connection->getLastReply()
                );
            }
            if (!$connection->hello($this->host)) {
                throw SmtpConnectionRuntimeException::createFromSmtpError(
                    'EHLO',
                    $connection
                );
            }
            $extensionList = $connection->getServerExtList();
            if (is_array($extensionList) && array_key_exists('STARTTLS', $extensionList)) {
                if (!$connection->startTLS()) {
                    throw SmtpConnectionRuntimeException::createFromSmtpError(
                        'STARTTLS',
                        $connection
                    );
                }
                // Repeat EHLO after STARTTLS
                if (!$connection->hello($mxServer)) {
                    throw SmtpConnectionRuntimeException::createFromSmtpError(
                        'EHLO (2)',
                        $connection
                    );
                }
            }
            if (!$connection->mail('john@' . $this->host)) {
                throw SmtpConnectionRuntimeException::createFromSmtpError(
                    'MAIL FROM',
                    $connection
                );
            }
            if (!$connection->recipient($email)) {
                throw SmtpConnectionRuntimeException::createFromSmtpError(
                    'RCPT TO',
                    $connection
                );
            }
            $connection->quit();

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
            $connection->quit();

            if (
                550 === $exception->getCode()
            ) {
                if ($this->bounceIsCausedByInactiveUserSpecification->isSatisfiedBy($exception->getLastReply())) {
                    return InvalidAddressDto::inactiveUser($email, $exception->getLastReply());
                }
                if ($this->bounceIsCausedByUnknownUserSpecification->isSatisfiedBy($exception->getLastReply())) {
                    return InvalidAddressDto::unknownUser($email, $exception->getLastReply());
                }
                return new UnverifiedAddressDto($email);
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
