<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\HardBounceIsCausedByOverQuotaSpecification;
use PHPUnit\Framework\TestCase;

class HardBounceIsCausedByOverQuotaSpecificationTest extends TestCase
{
    /** @dataProvider provideMessages */
    public function testSpecificationWorks(string $message, bool $isSpam): void
    {
        $spec = new HardBounceIsCausedByOverQuotaSpecification();
        self::assertSame($isSpam, $spec->isSatisfiedBy($message));
    }

    /** @return array{string, bool}[] */
    public function provideMessages(): iterable
    {
        yield ['Email rejected per SPAM policy', false];
        yield [<<<MESSAGE
554 5.2.2 <xxx@free.fr>: Recipient address rejected: Quota exceeded (mailbox for user is full)
MESSAGE, true];
    }
}
