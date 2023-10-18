<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Translatable;

use AssoConnect\SmtpToolbox\Translatable\BounceReasonTranslatable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class BounceReasonTranslatableTest extends KernelTestCase
{
    /**
     * @dataProvider providerTrans
     */
    public function testBounceReasonTranslatable(string $reason, string $expectedTranslationKey): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);

        self::assertSame(
            $translator->trans($expectedTranslationKey, [], 'assoconnect_smtp_toolbox'),
            (new BounceReasonTranslatable($reason))->trans($translator)
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function providerTrans(): iterable
    {
        yield 'code' => [
            '2.2.1 test code',
            'bounce.reason.none',
        ];

        yield 'text' => [
            'Not delivering to a user who marked your messages as spam',
            'bounce.reason.spam',
        ];

        yield 'unknown' => [
            'value does not exists',
            'bounce.reason.none',
        ];
    }
}
