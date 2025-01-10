<?php

namespace Optime\Sso\Bundle\Client\Factory;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserFactoryInterface
{
    public function create(SsoData $ssoData): ?UserFactoryResult;
}