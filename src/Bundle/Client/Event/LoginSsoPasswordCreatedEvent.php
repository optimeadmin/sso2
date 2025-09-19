<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Client\Event;

use Optime\Sso\Bundle\Client\Factory\UserFactoryResult;
use Optime\Sso\Bundle\Client\Security\SsoData;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\EventDispatcher\Event;

class LoginSsoPasswordCreatedEvent extends Event
{
    public function __construct(
        public readonly Passport $passport,
        public readonly UserFactoryResult $userData,
        public readonly SsoData $ssoData,
    ) {
    }
}