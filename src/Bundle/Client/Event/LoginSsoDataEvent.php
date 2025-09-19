<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Client\Event;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Symfony\Contracts\EventDispatcher\Event;

class LoginSsoDataEvent extends Event
{
    public function __construct(public readonly SsoData $ssoData)
    {
    }
}