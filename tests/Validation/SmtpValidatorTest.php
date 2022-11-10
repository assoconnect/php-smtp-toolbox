<?php

namespace AssoConnect\SmtpToolbox\Tests\Validation;

use AssoConnect\SmtpToolbox\Connection\SmtpConnection;
use AssoConnect\SmtpToolbox\Dto\InvalidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidAddressDto;
use AssoConnect\SmtpToolbox\Dto\ValidationStatusDtoInterface;
use AssoConnect\SmtpToolbox\ProviderClient\GenericProviderClient;
use AssoConnect\SmtpToolbox\Specification\ExceptionComesFromTemporaryFailureSpecification;
use AssoConnect\SmtpToolbox\Tests\Resolver\MxServersResolverTestFactory;
use AssoConnect\SmtpToolbox\Validation\SmtpValidator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SmtpValidatorTest extends TestCase
{
    private SmtpValidator $validator;

    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->validator = new SmtpValidator(
            MxServersResolverTestFactory::create(),
            new GenericProviderClient(
                new SmtpConnection($logger),
                new ExceptionComesFromTemporaryFailureSpecification(),
                'hello.org'
            ),
            $logger
        );
    }

    /**
     * @dataProvider provideEmailAddresses
     * @param class-string<ValidationStatusDtoInterface> $expectedDtoClass
     */
    public function testClientWorks(string $email, string $expectedDtoClass): void
    {
        self::assertInstanceOf($expectedDtoClass, $this->validator->validate($email));
    }

    /** @return array{0: string, 1: class-string<ValidationStatusDtoInterface>}[] */
    public function provideEmailAddresses(): iterable
    {
        yield ['this_user_does_not_exist', InvalidAddressDto::class];
        yield ['this_user_does_not_exist@this_domain_does_not_exist', InvalidAddressDto::class];
        yield ['this_user_does_not_exist@gmail.com', InvalidAddressDto::class];

        yield ['sylvain.fabre@assoconnect.com', ValidAddressDto::class];
    }
}
