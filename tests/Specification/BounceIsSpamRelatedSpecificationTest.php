<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Resolver\BounceTypeResolver;
use AssoConnect\SmtpToolbox\Specification\BounceIsSpamRelatedSpecification;
use PHPUnit\Framework\TestCase;

class BounceIsSpamRelatedSpecificationTest extends TestCase
{
    /** @dataProvider provideMessages */
    public function testSpecificationWorks(string $message, bool $isSpam): void
    {
        $spec = new BounceIsSpamRelatedSpecification(new BounceTypeResolver());
        self::assertSame($isSpam, $spec->isSatisfiedBy($message));
    }

    /** @return array{string, bool}[] */
    public function provideMessages(): iterable
    {
        yield ['Email rejected per SPAM policy', true];
        yield ['Sender address rejected: Sender user unknown.  Adresse expediteur inconnue', false];
    }
}
