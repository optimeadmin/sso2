<?php

namespace Optime\Sso\Bundle\Client\Api;

use Optime\Sso\Bundle\Client\Exception\InvalidTokenException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SsoApiTokensProvider
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getToken(): string
    {
        return $this->getTokens()[0];
    }

    public function getRefreshToken(): string
    {
        return $this->getTokens()[1];
    }

    public function getAuthHeaders(): array
    {
        return ['sso-api-auth-token' => $this->getToken()];
    }

    public function getRefreshTokensHeaders(): array
    {
        return ['sso-api-refresh-token' => $this->getRefreshToken()];
    }

    public function getServerUrl(): string
    {
        $token = $this->validateAndGetToken();

        if (!$token->hasAttribute('sso_server_url')) {

            throw new InvalidTokenException('sso_server_url attribute not found');
        }

        return $token->getAttribute('sso_server_url');
    }

    public function getRefreshTokenUrl(): string
    {
        $token = $this->validateAndGetToken();

        if (!$token->hasAttribute('sso_refresh_token_url')) {

            throw new InvalidTokenException('sso_refresh_token_url attribute not found');
        }

        return $token->getAttribute('sso_refresh_token_url');
    }

    public function refresh(array $apiTokens): void
    {
        $token = $this->validateAndGetToken();
        $token->setAttribute('sso_api_tokens', $apiTokens);
    }

    private function validateAndGetToken(): TokenInterface
    {
        if (!$token = $this->security->getToken()) {
            throw new InvalidTokenException('Token not found');
        }

        if ($token->hasAttribute('is_local') && $token->getAttribute('is_local')) {
            throw new InvalidTokenException('Local sso cannot be used for apiTokens');
        }

        return $token;
    }

    private function getTokens(): ?array
    {
        $token = $this->validateAndGetToken();

        if (!$token->hasAttribute('sso_api_tokens')) {

            throw new InvalidTokenException('sso_api_tokens attribute not found');
        }

        $tokens = $token->getAttribute('sso_api_tokens');

        if (!is_array($tokens) || count($tokens) !== 2) {
            throw new InvalidTokenException('unrecognized sso_api_tokens attribute value');
        }

        return $tokens;
    }
}