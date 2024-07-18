<?php

namespace Origami\GoogleAuth\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Pool implements CacheItemPoolInterface
{
    protected Repository $cache;

    protected $tags = ['google-auth'];

    protected array $deferred = [];

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function getItem(string $key): Item
    {
        $value = $this->cache->tags($this->tags)->get($key);
        Log::debug('AuthCache getItem', ['key' => $key, 'value' => $value]);

        return $value ?: Item::miss($key);
    }

    public function getItems(array $keys = []): iterable
    {
        return collect($keys)->map(function (string $key) {
            return $this->getItem($key);
        });
    }

    public function hasItem(string $key): bool
    {
        Log::debug('AuthCache hasItem', ['key' => $key]);

        return $this->getItem($key)->isHit();
    }

    public function clear(): bool
    {
        return $this->cache->tags($this->tags)->flush();
    }

    public function deleteItem(string $key): bool
    {
        Log::debug('AuthCache deleteItem', ['key' => $key]);

        return $this->cache->tags($this->tags)->forget($key);
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($this->keys as $key) {
            if (! $this->deleteItem($key)) {
                return false;
            }
        }

        return true;
    }

    public function save(Item|CacheItemInterface $item): bool
    {
        Log::debug('AuthCache save', ['item' => $item]);

        return $this->cache->tags($this->tags)->put($item->getKey(), $item, $item->expiration);
    }

    public function saveDeferred(Item|CacheItemInterface $item): bool
    {
        $this->deferred[] = $item;

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            if (! $this->save($item)) {
                return false;
            }
        }

        return true;
    }
}
