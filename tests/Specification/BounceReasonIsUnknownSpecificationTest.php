<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\BounceReasonIsUnknownSpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BounceReasonIsUnknownSpecificationTest extends TestCase
{
    #[DataProvider('provideMessages')]
    public function testSpecificationWorks(string $message, bool $isSpam): void
    {
        $spec = new BounceReasonIsUnknownSpecification();
        self::assertSame($isSpam, $spec->isSatisfiedBy($message));
    }

    /** @return array{string, bool}[] */
    public static function provideMessages(): iterable
    {
        yield ['hard bounce', true];
        yield ['Sender address rejected: Sender user unknown.  Adresse expediteur inconnue', false];
    }
}
