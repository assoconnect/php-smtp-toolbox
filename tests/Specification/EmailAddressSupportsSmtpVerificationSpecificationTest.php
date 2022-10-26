<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\EmailAddressSupportsSmtpVerificationSpecification;
use AssoConnect\SmtpToolbox\Tests\ProviderChecker\ProviderCheckerTestFactory;
use AssoConnect\SmtpToolbox\Tests\Resolver\MxServersResolverTestFactory;
use PHPUnit\Framework\TestCase;

class EmailAddressSupportsSmtpVerificationSpecificationTest extends TestCase
{
    /** @dataProvider provideEmailAddresses */
    public function testSpecificationWorks(string $email, bool $isSatisfied): void
    {
        $specification = new EmailAddressSupportsSmtpVerificationSpecification(
            ProviderCheckerTestFactory::create(),
            MxServersResolverTestFactory::create()
        );
        self::assertSame($isSatisfied, $specification->isSatisfiedBy($email));
    }

    /** @return array{string, bool}[] */
    public function provideEmailAddresses(): iterable
    {
        // Supported email addresses
        yield ['test@sfr.fr', true];
        yield ['test@laposte.net', true];
        yield ['test@noos.fr', true];

        // Unsupported email addresses
        yield ['invalid email', false];
        yield ['test@gmail.com', false];
    }
}
