<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\EmailAddressIsFromAProviderSpecification;
use AssoConnect\SmtpToolbox\Tests\Resolver\MxServersResolverTestFactory;
use PHPUnit\Framework\TestCase;

class EmailAddressIsFromAProviderSpecificationTest extends TestCase
{
    /** @dataProvider provideEmailAddresses */
    public function testSpecificationWorks(string $emailAddress, string $providerName, bool $isSatisfiedBy): void
    {
        $spec = new EmailAddressIsFromAProviderSpecification(
            MxServersResolverTestFactory::create()
        );
        self::assertSame($isSatisfiedBy, $spec->isSatisfiedBy($emailAddress, $providerName, true));
    }

    public function provideEmailAddresses(): iterable
    {
        yield ['invalid email', '', false];
        yield ['unknow provider', 'provider that does not exist', false];

        // Without DNS check
        yield ['test@laposte.net', 'laposte', true];
        yield ['test@laposte.net', 'sfr', false];

        // Any TLD
        yield ['test@hotmail.abc', 'microsoft', true];

        // With DNS check
        yield ['test@assoconnect.com', 'google', true];
    }
}
