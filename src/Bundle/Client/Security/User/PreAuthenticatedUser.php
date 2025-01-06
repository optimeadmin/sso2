<?php

namespace Optime\Sso\Bundle\Client\Security\User;

use Optime\Sso\User\CompanyUserData;
use Symfony\Component\Security\Core\User\UserInterface;

class PreAuthenticatedUser implements UserInterface
{
    public function __construct(
        public readonly string $serverCode,
        public readonly CompanyUserData $companyUserData
    ) {
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return '123';
    }
}