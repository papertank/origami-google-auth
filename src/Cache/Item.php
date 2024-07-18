<?php

namespace Origami\GoogleAuth\Cache;

use DateTime;
use Psr\Cache\CacheItemInterface;

final class Item implements \Serializable, CacheItemInterface
{
    public ?string $key;

    public mixed $value;

    public ?\DateTimeInterface $expiration;

    protected bool $hit = false;

    public function __construct(string $key = null, mixed $value, \DateTimeInterface $expiration = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->expiration = $expiration;
    }

    public static function miss(string $key): static
    {
        return new self($key, null);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        if (! $this->isHit()) {
            return null;
        }

        return $this->value;
    }

    public function isHit(): bool
    {
        return ! is_null($this->key);
    }

    public function getExpiration(): ?\DateTimeInterface
    {
        return $this->expiration;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->hit = true;

        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        if (is_int($time)) {
            $time = new \DateInterval('PT'.$time.'S');
        }

        return $this->expiresAt($time ? (new DateTime)->add($time) : null);
    }

    public function serialize()
    {
        return json_encode($this->__serialize());
    }

    public function unserialize($data)
    {
        $data = json_decode($data, true);

        $this->__unserialize($data);
    }

    public function toArray()
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'expiration' => $this->expiration,
            'hit' => $this->hit,
        ];
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->key = $data['key'];
        $this->value = $data['value'];
        $this->expiration = $data['expiration'];
        $this->hit = $data['hit'];
    }
}
