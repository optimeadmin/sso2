<?php

namespace Optime\Sso\Bundle\Client\Security;

use Optime\Sso\User\CompanyUserData;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SsoDataProvider
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function byToken(string $token, string $url): SsoData
    {
        $response = $this->httpClient->request(
            'POST',
            $url,
            [
                'headers' => ['sso-token' => $token],
            ]
        );

        if ($response->getStatusCode() >= 400) {
            throw new AuthenticationException(strip_tags($response->getContent(false)));
        }

        $data = $response->toArray(false) ?? [];

        if (!isset($data['serverCode'])) {
            throw new AuthenticationException('serverCode is required in auth server response');
        }

        if (!isset($data['userData'])) {
            throw new AuthenticationException('userData is required in auth server response');
        }

        return new SsoData(
            $data['serverCode'],
            CompanyUserData::fromArray($data['userData']),
        );
    }
}