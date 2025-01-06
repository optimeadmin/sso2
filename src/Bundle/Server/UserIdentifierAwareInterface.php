<?php

namespace Optime\Sso\Bundle\Server;

interface UserIdentifierAwareInterface
{
    public function getSsoIdentifier(): string|int;
}