<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\ProviderChecker;

use AssoConnect\SmtpToolbox\Connection\SmtpConnection;
use AssoConnect\SmtpToolbox\ProviderChecker\AbstractProviderChecker;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ProviderCheckerTest extends TestCase
{
    /**
     * @dataProvider provideCheckersAndEmails
     * @param class-string<AbstractProviderChecker> $checkerName
     */
    public function testProviderWorks(string $checkerName, string $email, bool $isValid): void
    {
        $checker = new $checkerName(
            new SmtpConnection(new NullLogger())
        );
        self::assertSame($isValid, $checker->check($email));
    }

    /**
     * @return array{class-string<AbstractProviderChecker>, string, bool}[]
     */
    public function provideCheckersAndEmails(): iterable
    {
        foreach (ProviderCheckerTestFactory::CLASSES as $class) {
            foreach ($class::VALID_EXAMPLES as $valid) {
                yield $valid => [$class, $valid, true];
            }
            foreach ($this->getDomains($class) as $domain) {
                $invalid = 'thisuserdoesntexist@' . $domain;
                yield $invalid => [$class, $invalid, false];
            }
        }
    }

    /**
     * @param class-string<AbstractProviderChecker> $implementation
     * @return string[]
     */
    private function getDomains(string $implementation): array
    {
        if ([] !== $implementation::DOMAINS) {
            return $implementation::DOMAINS;
        }

        return array_map(function (string $email): string {
            return explode('@', $email)[1];
        }, $implementation::VALID_EXAMPLES);
    }
}
