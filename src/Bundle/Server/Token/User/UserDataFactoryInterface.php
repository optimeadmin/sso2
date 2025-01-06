<?php

namespace Optime\Sso\Bundle\Server\Token\User;

use Optime\Sso\User\CompanyUserData;

interface UserDataFactoryInterface
{
    public function create(object $user): CompanyUserData;
}