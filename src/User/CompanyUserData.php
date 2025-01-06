<?php

namespace Optime\Sso\User;

class CompanyUserData
{
    public function __construct(
        public readonly CompanyData $company,
        public readonly UserData $user,
        public readonly ProfileData $profile,
    ) {
    }

    public function toArray(): array
    {
        return [
            'company' => $this->company->toArray(),
            'user' => $this->user->toArray(),
            'profile' => $this->profile->toArray(),
        ];
    }
}