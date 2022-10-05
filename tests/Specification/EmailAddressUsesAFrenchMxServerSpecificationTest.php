<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\EmailAddressUsesAFrenchMxServerSpecification;
use AssoConnect\SmtpToolbox\Tests\Resolver\MxServersResolverTestFactory;
use PHPUnit\Framework\TestCase;

class EmailAddressUsesAFrenchMxServerSpecificationTest extends TestCase
{
    /** @dataProvider provideEmailAddresses */
    public function testSpecificationWorks(string $emailAddress, bool $usesFrenchMxServers): void
    {
        $spec = new EmailAddressUsesAFrenchMxServerSpecification(
            MxServersResolverTestFactory::create()
        );
        self::assertSame($usesFrenchMxServers, $spec->isSatisfiedBy($emailAddress));
    }

    /** @return array{string, bool}[] */
    public function provideEmailAddresses(): iterable
    {
        yield ['test@gmail.com', false];
        yield ['test@hotmail.com', false];
        yield ['test@assoconnect.com', false];

        yield ['test@laposte.net', true];
        yield ['test@sfr.fr', true];
    }
}
