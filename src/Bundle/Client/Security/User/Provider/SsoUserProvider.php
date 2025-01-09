<?php

namespace Optime\Sso\Bundle\Client\Security\User\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Optime\Sso\Bundle\Client\Security\User\CustomRefreshUserInterface;
use Optime\Sso\Bundle\Client\Security\User\BasicSsoUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SsoUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof CustomRefreshUserInterface) {
            return $user->refreshSsoUser($this->entityManager);
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === BasicSsoUser::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new \LogicException("Use SsoDataProvider");
    }
}