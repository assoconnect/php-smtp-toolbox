<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\ProviderChecker;

use AssoConnect\SmtpToolbox\Connection\SmtpConnection;
use AssoConnect\SmtpToolbox\ProviderChecker\OvhProviderChecker;
use AssoConnect\SmtpToolbox\ProviderChecker\LaposteProviderChecker;
use AssoConnect\SmtpToolbox\ProviderChecker\ProviderCheckerInterface;
use AssoConnect\SmtpToolbox\ProviderChecker\SfrProviderChecker;
use Psr\Log\NullLogger;

class ProviderCheckerTestFactory
{
    public const IMPLEMENTATIONS = [
        LaposteProviderChecker::class,
        OvhProviderChecker::class,
        SfrProviderChecker::class,
    ];

    /** @return ProviderCheckerInterface[] */
    public static function create(): array
    {
        return array_map(function (string $class): ProviderCheckerInterface {
            return new $class(new SmtpConnection(new NullLogger()));
        }, self::IMPLEMENTATIONS);
    }
}
