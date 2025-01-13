<?php

namespace Optime\Sso\Bundle\Client\Security\Local;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\Bundle\Client\Token\LocalTokenGenerator;

class LocalSsoDataProvider
{
    public function __construct(
        private readonly LocalSsoDataFactoryInterface $dataProvider,
        private readonly LocalTokenGenerator $tokenGenerator,
    ) {
    }

    public function resolve(string $token): SsoData
    {
        $this->tokenGenerator->decodeToken($token);

        return $this->dataProvider->getLocalData();
    }
}