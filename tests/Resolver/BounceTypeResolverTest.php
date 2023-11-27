<?php

namespace AssoConnect\SmtpToolbox\Tests\Resolver;

use AssoConnect\SmtpToolbox\Resolver\BounceTypeResolver;
use PHPUnit\Framework\TestCase;

class BounceTypeResolverTest extends TestCase
{
    /** @dataProvider provideBounceReasons */
    public function testResolveBounceMessagesCorrectly($expected, $bounceReason): void
    {
        $resolver = new BounceTypeResolver();

        self::assertEquals($expected, $resolver->resolve($bounceReason));
    }

    public function provideBounceReasons(): iterable
    {
        yield 'DMarc failure' => [BounceTypeResolver::BOUNCE_REASON_DMARC_FAILURE, 'Email rejected per DMARC policy'];
        yield 'Blacklisted' => [BounceTypeResolver::BOUNCE_REASON_BLACKLISTED, 'LPN007_510'];
        yield 'Invalid email' => [
            BounceTypeResolver::BOUNCE_REASON_INVALID,
            'mailbox is inactive and has been disabled'
        ];
        yield 'Unknown scenario' => [BounceTypeResolver::BOUNCE_REASON_UNKNOWN, 'Relay access denied'];
        yield 'Unqualified reason' => [BounceTypeResolver::BOUNCE_REASON_UNQUALIFIED, 'toto'];
    }
}