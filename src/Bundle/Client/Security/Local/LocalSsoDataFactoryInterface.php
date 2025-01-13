<?php

namespace Optime\Sso\Bundle\Client\Security\Local;

use Optime\Sso\Bundle\Client\Security\SsoData;

interface LocalSsoDataFactoryInterface
{
    public function getLocalData(): SsoData;
}