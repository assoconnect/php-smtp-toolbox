<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\EmailAddressUsesAnUSMxServerSpecification;
use AssoConnect\SmtpToolbox\Tests\Resolver\MxServersResolverTestFactory;
use PHPUnit\Framework\TestCase;

class EmailAddressUsesAnUSMxServerSpecificationTest extends TestCase
{
    /** @dataProvider provideEmailAddresses */
    public function testSpecificationWorks(string $emailAddress, bool $usesFrenchMxServers): void
    {
        $spec = new EmailAddressUsesAnUSMxServerSpecification(
            MxServersResolverTestFactory::create()
        );
        self::assertSame($usesFrenchMxServers, $spec->isSatisfiedBy($emailAddress));
    }

    /** @return array{string, bool}[] */
    public function provideEmailAddresses(): iterable
    {
        yield ['test@laposte.net', false];
        yield ['test@sfr.fr', false];
        yield ['invalid email', false];

        yield ['test@gmail.com', true];
        yield ['test@hotmail.com', true];
        yield ['test@assoconnect.com', true];
        yield ['contact@medireport.fr', true];
    }
}
