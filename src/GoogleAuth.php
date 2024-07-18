<?php

namespace Origami\GoogleAuth;

use Google\Auth\CredentialsLoader;
use Google\Auth\FetchAuthTokenCache;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Arr;
use Origami\GoogleAuth\Cache\Pool;
use Origami\GoogleAuth\Exceptions\InvalidConfiguration;

class GoogleAuth
{
    protected array $config;

    protected Pool $cache;

    public function __construct(array $config, Pool $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    public function client(array $options = [], array $scopes = [])
    {
        if (! isset($options['handler'])) {
            $options['handler'] = HandlerStack::create();
        }

        $options['handler']->push($this->authMiddleware($scopes));
        $options['auth'] = 'google_auth';

        return new Client($options);
    }

    public function authMiddleware(array|string $scopes = [])
    {
        return new AuthTokenMiddleware($this->authToken($scopes));
    }

    public function authToken(array|string $scopes = [], $defaultScope = null)
    {
        $auth = CredentialsLoader::makeCredentials($scopes, $this->credentials(), $defaultScope);

        if (Arr::get($this->config, 'cache.enabled', false)) {
            return new FetchAuthTokenCache(
                $auth,
                Arr::get($this->config, 'cache.options', []),
                $this->cache
            );
        }

        return $auth;
    }

    public function credentials(): array
    {
        $credentials = Arr::get($this->config, 'service_account.credentials');

        if (! is_array($credentials) && ! is_string($credentials)) {
            throw InvalidConfiguration::credentialsTypeWrong($credentials);
        }

        if (is_string($credentials) && ! file_exists($credentials)) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($credentials);
        }

        if (is_string($credentials)) {
            $credentials = json_decode(file_get_contents($credentials), true);
        }

        return $credentials;
    }
}
