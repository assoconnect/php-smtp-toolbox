<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Resolver;

use AssoConnect\SmtpToolbox\Resolver\ProviderResolver;
use AssoConnect\SmtpToolbox\Specification\EmailAddressIsFromAProviderSpecification;
use PHPUnit\Framework\TestCase;

class ProviderResolverTest extends TestCase
{
    /**
     * @dataProvider provideAddressesAndProviders
     */
    public function testProviderResolverReturnsTheRightProvider(string $address, ?string $provider): void
    {
        $resolver = new ProviderResolver(
            new EmailAddressIsFromAProviderSpecification(MxServersResolverTestFactory::create())
        );

        self::assertSame($provider, $resolver->resolve($address));
    }

    public function provideAddressesAndProviders(): iterable
    {
        // Without DNS check
        yield ['john@hotmail.com', 'microsoft'];

        // With DNS check
        yield ['john@assoconnect.com', 'google'];

        // Unknown provider
        yield ['john', null];
        yield ['john@domain.abc', null];
    }
}
