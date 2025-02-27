<?php

namespace Optime\Sso\Bundle\Client\Security;

use Optime\Sso\User\CompanyUserData;

class SsoData implements \JsonSerializable
{
    public function __construct(
        public readonly string $serverCode,
        public readonly CompanyUserData $companyUserData,
        public readonly ?array $apiTokens = null,
        public readonly ?string $serverUrl = null,
        public readonly ?string $refreshTokenUrl = null,
    ) {
    }

    public function getRoles(): array
    {
        return $this->companyUserData->user->roles ?? ['ROLE_USER'];
    }

    public function getUserIdentifier(): string
    {
        return $this->companyUserData->user->usernameOrEmail;
    }

    public function jsonSerialize(): array
    {
        return [
            'serverCode' => $this->serverCode,
            'data' => $this->companyUserData->toArray(),
        ];
    }
}