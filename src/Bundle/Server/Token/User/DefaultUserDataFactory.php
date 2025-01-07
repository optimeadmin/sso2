<?php

namespace Optime\Sso\Bundle\Server\Token\User;

use Optime\Sso\Bundle\Server\Token\User\UserDataFactoryInterface;
use Optime\Sso\User\CompanyData;
use Optime\Sso\User\CompanyUserData;
use Optime\Sso\User\ProfileData;
use Optime\Sso\User\UserData;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultUserDataFactory implements UserDataFactoryInterface
{

    public function create(UserInterface $user): CompanyUserData
    {
        return new CompanyUserData(
            new CompanyData(0, ''),
            new UserData(0, $user->getUserIdentifier(), roles: $user->getRoles()),
            new ProfileData(0, ''),
        );
    }
}