<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Specification;

use AssoConnect\SmtpToolbox\Specification\HardBounceIsCausedByInactiveUserSpecification;
use PHPUnit\Framework\TestCase;

class HardBounceIsCausedByInactiveUserSpecificationTest extends TestCase
{
    /** @dataProvider provideMessages */
    public function testSpecificationWorks(string $message, bool $isSpam): void
    {
        $spec = new HardBounceIsCausedByInactiveUserSpecification();
        self::assertSame($isSpam, $spec->isSatisfiedBy($message));
    }

    /** @return array{string, bool}[] */
    public function provideMessages(): iterable
    {
        yield ['Email rejected per SPAM policy', false];
        yield [<<<MESSAGE
450 4.2.1 <xxx@laposte.net>: Recipient address rejected: this mailbox is inactive and has been disabled
MESSAGE, true];
    }
}
