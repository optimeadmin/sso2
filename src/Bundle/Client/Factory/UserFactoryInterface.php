<?php

namespace Optime\Sso\Bundle\Client\Factory;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserFactoryInterface
{
    public function create(SsoData $ssoData): ?UserInterface;

    public function getRoles(UserInterface $user, SsoData $ssoData): array;
}