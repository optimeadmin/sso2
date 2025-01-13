<?php

namespace Optime\Sso\Bundle\Client\Security\Local;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\User\CompanyUserData;

interface LocalSsoDataFactoryInterface
{
    public function getLocalData(): SsoData|CompanyUserData;
}