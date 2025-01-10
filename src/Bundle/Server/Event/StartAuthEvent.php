<?php

namespace Optime\Sso\Bundle\Server\Event;

use Optime\Sso\Bundle\Server\Entity\UserToken;
use Symfony\Contracts\EventDispatcher\Event;

class StartAuthEvent extends Event
{
    public function __construct(public readonly UserToken $token)
    {
    }
}