<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Resolver;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class MxServersResolver
{
    public const CACHE_KEY = 'mx_servers_';

    private AdapterInterface $cache;

    public function __construct(AdapterInterface $sharedCacheAdapter)
    {
        $this->cache = $sharedCacheAdapter;
    }

    /**
     * @return string[]|null
     */
    public function getMxServers(string $domain): ?array
    {
        //We store mxcheck result in cache
        $cacheItem = $this->cache->getItem(self::CACHE_KEY . $domain);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        if (!checkdnsrr($domain)) {
            return null;
        }

        getmxrr($domain, $hosts);
        $cacheItem->set($hosts);
        $this->cache->save($cacheItem);

        return $hosts;
    }
}
