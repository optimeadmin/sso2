<?php

namespace Optime\Sso\Bundle\Client\Api;

use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class SsoHttpClient implements HttpClientInterface
{
    use HttpClientTrait;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly SsoApiTokensProvider $tokensProvider,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $options['headers'] = [
            ...($options['headers'] ?? []),
            ...$this->tokensProvider->getAuthHeaders(),
        ];

        $options['base_uri'] ??= $this->tokensProvider->getServerUrl();

        return $this->client->request($method, $url, $options);
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }
}