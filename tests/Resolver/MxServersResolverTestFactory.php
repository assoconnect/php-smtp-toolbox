<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Resolver;

use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class MxServersResolverTestFactory
{
    public static function create(): MxServersResolver
    {
        return new MxServersResolver(new ArrayAdapter());
    }
}
