<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\EmailAddressIsFromACountrySpecification;
use AssoConnect\SmtpToolbox\Specification\EmailAddressIsFromAProviderSpecification;
use AssoConnect\SmtpToolbox\Tests\Resolver\MxServersResolverTestFactory;
use PHPUnit\Framework\TestCase;

class EmailAddressIsFromACountrySpecificationTest extends TestCase
{
    /** @dataProvider provideEmailAddresses */
    public function testSpecificationWorks(string $emailAddress, string $country, bool $isSatisfiedBy): void
    {
        $spec = new EmailAddressIsFromACountrySpecification(
            new EmailAddressIsFromAProviderSpecification(
                MxServersResolverTestFactory::create()
            )
        );
        self::assertSame($isSatisfiedBy, $spec->isSatisfiedBy($emailAddress, $country));
    }

    public function provideEmailAddresses(): iterable
    {
        // Invalid
        yield ['hello', 'FR', false];
        yield ['hello', 'US', false];

        // US Domain
        yield ['test@gmail.com', 'FR', false];
        yield ['test@gmail.com', 'US', true];
        // US Domain with MX check
        yield ['test@assoconnect.com', 'US', true];

        // FR Domain
        yield ['test@laposte.net', 'FR', true];
        yield ['test@laposte.net', 'US', false];
    }
}
