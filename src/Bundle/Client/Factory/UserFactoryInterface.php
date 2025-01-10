<?php

namespace Optime\Sso\Bundle\Client\Factory;

use Optime\Sso\Bundle\Client\Security\SsoData;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserFactoryInterface
{
    public function create(SsoData $ssoData): ?UserFactoryResult;

    public function configureOptions(
        OptionsResolver $companyOptions,
        OptionsResolver $userOptions,
        OptionsResolver $profileOptions,
        OptionsResolver $extraOptions,
    ): void;
}