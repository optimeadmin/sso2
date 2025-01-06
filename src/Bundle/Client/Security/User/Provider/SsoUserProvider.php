<?php

namespace Optime\Sso\Bundle\Client\Security\User\Provider;

use Optime\Sso\Bundle\Client\Security\User\PreAuthenticatedUser;
use Optime\Sso\User\CompanyUserData;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SsoUserProvider implements UserProviderInterface
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === PreAuthenticatedUser::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new \LogicException("Use getUserByToken");
    }

    public function getUserByToken(string $token, string $url): UserInterface
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

        return new PreAuthenticatedUser(
            $data['serverCode'],
            CompanyUserData::fromArray($data['userData']),
        );
    }
}