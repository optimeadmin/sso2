<?php

namespace Optime\Sso\Bundle\Client\Factory;

use Symfony\Component\Security\Core\User\UserInterface;

class UserFactoryResult
{
    public function __construct(
        private readonly ?UserInterface $user,
        private ?array $roles = null,
        private readonly array $attributes = [],
    ) {
        if ($this->user && null === $this->roles) {
            $this->roles = $this->user->getRoles();
        }
    }

    public static function userNotFound(): self
    {
        return new self(null);
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}