<?php

namespace Optime\Sso\Bundle\Client\Security\User;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Symfony\Component\Security\Core\User\UserInterface;

class BasicSsoUser implements UserInterface
{
    public function __construct(
        public readonly SsoData $ssoData,
    ) {
    }

    public function getRoles(): array
    {
        return $this->ssoData->getRoles();
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->ssoData->getUserIdentifier();
    }
}