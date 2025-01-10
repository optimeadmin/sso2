<?php

namespace Optime\Sso\Bundle\Client\Factory;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\Bundle\Client\Security\User\BasicSsoUser;
use Symfony\Component\Security\Core\User\UserInterface;

class UserFactory implements UserFactoryInterface
{
    public function create(SsoData $ssoData): UserFactoryResult
    {
        return new UserFactoryResult(new BasicSsoUser($ssoData));
    }
}