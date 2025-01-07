<?php

namespace Optime\Sso\Bundle\Server\Token\User;

use Optime\Sso\User\CompanyUserData;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserDataFactoryInterface
{
    public function create(UserInterface $user): CompanyUserData;
}