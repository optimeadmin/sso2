<?php

namespace Optime\Sso\User;

class CompanyData
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly array $extraData = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'base' => [
                'id' => $this->id,
                'name' => $this->name,
            ],
            'extra' => $this->extraData,
        ];
    }
}