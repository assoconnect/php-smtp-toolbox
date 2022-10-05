<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\HardBounceIsCausedByUnknownUserSpecification;
use PHPUnit\Framework\TestCase;

class HardBounceIsCausedByUnknownUserSpecificationTest extends TestCase
{
    /** @dataProvider provideMessages */
    public function testSpecificationWorks(string $message, bool $isSpam): void
    {
        $spec = new HardBounceIsCausedByUnknownUserSpecification();
        self::assertSame($isSpam, $spec->isSatisfiedBy($message));
    }

    /** @return array{string, bool}[] */
    public function provideMessages(): iterable
    {
        yield ['Email rejected per SPAM policy', false];
        yield ['Sender address rejected: Sender user unknown.  Adresse expediteur inconnue', true];
    }
}
