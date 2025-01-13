<?php

namespace Optime\Sso\Bundle\Client\Security\Local;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\Bundle\Client\Token\LocalTokenGenerator;
use Optime\Sso\User\CompanyUserData;

class LocalSsoDataProvider
{
    public function __construct(
        private readonly LocalSsoDataFactoryInterface $dataProvider,
        private readonly LocalTokenGenerator $tokenGenerator,
    ) {
    }

    public function resolve(string $token): SsoData
    {
        $serverCode = $this->tokenGenerator->decodeToken($token);
        $data = $this->dataProvider->getLocalData($serverCode);

        if ($data instanceof CompanyUserData) {
            $data = new SsoData($serverCode, $data);
        }

        return $data;
    }
}