<?php

namespace Optime\Sso\Bundle\Server;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserIdentifierAwareInterface extends UserInterface
{
    public function getSsoIdentifier(): string|int;
}