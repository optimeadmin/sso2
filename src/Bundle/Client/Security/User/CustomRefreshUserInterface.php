<?php

namespace Optime\Sso\Bundle\Client\Security\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface CustomRefreshUserInterface
{
    public function refreshSsoUser(EntityManagerInterface $entityManager): UserInterface;
}