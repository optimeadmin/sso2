<?php

namespace Optime\Sso\Component\Server;

interface UserIdentifierAwareInterface
{
    public function getSsoIdentifier(): string|int;
}