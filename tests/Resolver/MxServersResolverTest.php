<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests\Resolver;

use AssoConnect\SmtpToolbox\Resolver\MxServersResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\DnsMock;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class MxServersResolverTest extends TestCase
{
    private ArrayAdapter $cache;
    private MxServersResolver $resolver;

    public function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $this->resolver = new MxServersResolver($this->cache);
    }

    public function testCacheIsUsed(): void
    {
        $domain = 'ensemble2generations.fr';
        $cacheKey = MxServersResolver::CACHE_KEY . $domain;

        $isHitBefore = $this->cache->hasItem($cacheKey);
        $this->resolver->getMxServers($domain);
        $isHitAfter = $this->cache->hasItem($cacheKey);

        self::assertFalse($isHitBefore);
        self::assertTrue($isHitAfter);
    }

    /**
     * @group functional
     * @group dns-sensitive
     */
    public function testDns(): void
    {
        DnsMock::withMockedHosts([
            'mytestdomain.fr' => [
                [
                    'type' => 'MX',
                    'host' => 'my.mx.server',
                    'pri' => 100,
                ],
            ],
        ]);

        self::assertNull($this->resolver->getMxServers('anydomain.fr'));
        $servers = $this->resolver->getMxServers('mytestdomain.fr');
        self::assertNotNull($servers);
        self::assertContains('my.mx.server', $servers);
    }
}
