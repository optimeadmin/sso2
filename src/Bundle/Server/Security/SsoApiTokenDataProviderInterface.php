<?php

namespace Optime\Sso\Bundle\Server\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface SsoApiTokenDataProviderInterface
{
    public function getApiTokenData(UserInterface $user): array;
}