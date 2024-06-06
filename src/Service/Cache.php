<?php

namespace App\Service;

use Predis\Client as RedisClient;

class Cache
{
    private RedisClient $client;
    private int $ttl;

    public function __construct()
    {
        try {
            $this->client = new RedisClient([
                'scheme' => 'tcp',
                'host' => $_ENV['REDIS_HOST'],
                'port' => $_ENV['REDIS_PORT'],
            ]);
            $this->ttl = $_ENV['CACHE_TTL'];
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to connect to Redis: ' . $e->getMessage());
        }
    }

    public function get(string $key)
    {
        try {
            return $this->client->get($key);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to get value from cache: ' . $e->getMessage());
        }
    }

    public function set(string $key, $value): void
    {
        try {
            $this->client->set($key, $value);
            $this->client->expire($key, $this->ttl);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to set value in cache: ' . $e->getMessage());
        }
    }
}
