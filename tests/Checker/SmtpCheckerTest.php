<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Checker;

use AssoConnect\SmtpToolbox\Checker\SmtpChecker;
use AssoConnect\SmtpToolbox\Tests\ProviderChecker\ProviderCheckerTest;
use AssoConnect\SmtpToolbox\Tests\ProviderChecker\ProviderCheckerTestFactory;
use AssoConnect\SmtpToolbox\Tests\Resolver\MxServersResolverTestFactory;
use PHPUnit\Framework\TestCase;

class SmtpCheckerTest extends TestCase
{
    /** @dataProvider provideEmailAddresses */
    public function testCheckerValidatesEmail(string $email, ?bool $isValid): void
    {
        $checker = new SmtpChecker(
            ProviderCheckerTestFactory::create(),
            MxServersResolverTestFactory::create()
        );
        self::assertSame($isValid, $checker->check($email));
    }

    /** @return array{string, bool|null}[] */
    public function provideEmailAddresses(): iterable
    {
        // Supported email domains
        $test = new ProviderCheckerTest();
        foreach ($test->provideCheckersAndEmails() as $data) {
            yield [$data[1], $data[2]];
        }

        // Unsupported email domains
        yield ['test@gmail.com', null];
    }
}
