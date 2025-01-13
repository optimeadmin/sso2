<?php

namespace Optime\Sso\Bundle\Client\Security\Local;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\User\CompanyData;
use Optime\Sso\User\CompanyUserData;
use Optime\Sso\User\ProfileData;
use Optime\Sso\User\UserData;

class DefaultLocalDataFactory implements LocalSsoDataFactoryInterface
{
    public function getLocalData(): SsoData|CompanyUserData
    {
        $data = new CompanyUserData(
            new CompanyData(1, '_LOCAL_'),
            new UserData(
                1,
                '_LOCAL_USER_',
                [
                    'firstName' => 'Sso User',
                    'lastName' => 'Local',
                ],
                ['ROLE_USER'],
            ),
            new ProfileData(1, '_LOCAL_PROFILE'),
        );

        return new SsoData('local', $data);
    }
}