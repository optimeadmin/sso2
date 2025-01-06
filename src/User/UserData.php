<?php

namespace Optime\Sso\User;

class UserData
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $usernameOrEmail,
        public readonly array $extraData = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usernameOrEmail' => $this->usernameOrEmail,
            'extraData' => $this->extraData,
        ];
    }
}