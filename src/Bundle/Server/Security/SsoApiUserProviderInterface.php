<?php

namespace Optime\Sso\Bundle\Server\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface SsoApiUserProviderInterface
{
    public function loadUserFromSsoApiId(string|int $identifier): ?UserInterface;
}