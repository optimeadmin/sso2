<?php

namespace Optime\Sso\Bundle\Client\Api;

use Optime\Sso\Bundle\Client\Exception\InvalidTokenException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SsoApiTokenProvider
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getToken(): string
    {
        $token = $this->validateAndGetToken();

        if (!$token->hasAttribute('sso_api_token')) {
            throw new InvalidTokenException('sso_api_token attribute not found');
        }

        if (!$token->getAttribute('sso_api_token')) {
            throw new InvalidTokenException('sso_api_token attribute is empty');
        }

        return $token->getAttribute('sso_api_token');
    }

    public function getAuthHeaders(): array
    {
        return ['sso-api-auth-token' => $this->getToken()];
    }

    public function getServerUrl(): string
    {
        $token = $this->validateAndGetToken();

        if (!$token->hasAttribute('sso_server_url')) {
            throw new InvalidTokenException('sso_server_url attribute not found');
        }

        if (!$token->getAttribute('sso_server_url')) {
            throw new InvalidTokenException('sso_server_url attribute is empty');
        }

        return $token->getAttribute('sso_server_url');
    }

    private function validateAndGetToken(): TokenInterface
    {
        if (!$token = $this->security->getToken()) {
            throw new InvalidTokenException('Token not found');
        }

        if ($token->hasAttribute('is_local') && $token->getAttribute('is_local')) {
            throw new InvalidTokenException('Local sso cannot be used for apiToken');
        }

        return $token;
    }

    public function getAuthData(): array
    {
        return ['server_url' => $this->getServerUrl(), 'api_token' => $this->getToken()];
    }

    public function setAuthData(TokenInterface $token, array $data): void
    {
        $token->setAttribute('sso_api_token', $data['api_token'] ?? null);
        $token->setAttribute('sso_server_url', $data['server_url'] ?? null);
    }
}
