<?php

namespace Optime\Sso\Bundle\Client\Api;

use Optime\Sso\Bundle\Client\LocalServerChecker;
use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class SsoHttpClient implements HttpClientInterface
{
    use HttpClientTrait;

    private bool $tokenRefreshed = false;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly SsoApiTokensProvider $tokensProvider,
        private readonly LocalServerChecker $localServerChecker,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $options['headers'] = [
            ...($options['headers'] ?? []),
            ...$this->tokensProvider->getAuthHeaders(),
        ];

        $options['base_uri'] ??= $this->tokensProvider->getServerUrl();

        if ($this->localServerChecker->isLocalServer()) {
            $options['verify_peer'] = false;
        }

        $response = $this->client->request($method, $url, $options);

        if ($response->getStatusCode() === 401 && !$this->tokenRefreshed) {
            $message = $response->getContent(false);

            if (str_contains($message, 'token')) {
                $this->refreshTokens();
                $this->tokenRefreshed = true;

                return $this->request($method, $url, $options);
            }
        }

        $this->tokenRefreshed = false;

        return $response;
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function refreshTokens(): void
    {
        $options = [
            'headers' => $this->tokensProvider->getRefreshTokensHeaders(),
            'base_uri' => $this->tokensProvider->getRefreshTokenUrl(),
        ];

        if ($this->localServerChecker->isLocalServer()) {
            $options['verify_peer'] = false;
        }

        $response = $this->client->request('POST', '', $options);
        $data = $response->toArray(false);

        if ($data && count($data) > 0) {
            $this->tokensProvider->refresh($data);
        }
    }
}