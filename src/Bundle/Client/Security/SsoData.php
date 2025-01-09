<?php

namespace Optime\Sso\Bundle\Client\Security;

use Optime\Sso\User\CompanyUserData;

class SsoData
{
    public function __construct(
        public readonly string $serverCode,
        public readonly CompanyUserData $companyUserData
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
}