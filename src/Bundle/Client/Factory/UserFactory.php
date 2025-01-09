<?php

namespace Optime\Sso\Bundle\Client\Factory;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\Bundle\Client\Security\User\BasicSsoUser;
use Symfony\Component\Security\Core\User\UserInterface;

class UserFactory implements UserFactoryInterface
{

    public function create(SsoData $ssoData): UserInterface
    {
        return new BasicSsoUser($ssoData);
    }

    public function getRoles(UserInterface $user, SsoData $ssoData): array
    {
        return $user->getRoles();
    }
}