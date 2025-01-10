<?php

namespace Optime\Sso\User;

class UserData
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $usernameOrEmail,
        public readonly array $extraData = [],
        public readonly ?array $roles = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'base' => [
                'id' => $this->id,
                'usernameOrEmail' => $this->usernameOrEmail,
                'roles' => $this->roles,
            ],
            'extra' => $this->extraData,
        ];
    }
}