<?php

namespace Optime\Sso\User;

class CompanyUserData
{
    public function __construct(
        public readonly CompanyData $company,
        public readonly UserData $user,
        public readonly ProfileData $profile,
        public readonly array $extraData = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        $companyData = new CompanyData(
            $data['company']['id'] ?? 0,
            $data['company']['name'] ?? '',
            $data['company']['extraData'] ?? [],
        );
        $userData = new UserData(
            $data['user']['id'] ?? 0,
            $data['user']['usernameOrEmail'] ?? '',
            $data['user']['extraData'] ?? [],
            $data['user']['roles'] ?? null,
        );
        $profileData = new ProfileData(
            $data['profile']['id'] ?? 0,
            $data['profile']['name'] ?? '',
            $data['profile']['extraData'] ?? [],
        );

        return new self($companyData, $userData, $profileData, $data['extraData'] ?? []);
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