<?php

namespace Optime\Sso\Bundle\Client\Security;

use Optime\Sso\User\CompanyUserData;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SsoDataProvider
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

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
            throw new AuthenticationException($response->getContent(false));
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