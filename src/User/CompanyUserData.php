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
            $data['company']['base']['id'] ?? 0,
            $data['company']['base']['name'] ?? '',
            $data['company']['extra'] ?? [],
        );
        $userData = new UserData(
            $data['user']['base']['id'] ?? 0,
            $data['user']['base']['usernameOrEmail'] ?? '',
            $data['user']['extra'] ?? [],
            $data['user']['base']['roles'] ?? null,
        );
        $profileData = new ProfileData(
            $data['profile']['base']['id'] ?? 0,
            $data['profile']['base']['name'] ?? '',
            $data['profile']['extra'] ?? [],
        );

        return new self($companyData, $userData, $profileData, $data['extraData'] ?? []);
    }

    public function toArray(): array
    {
        return [
            'company' => $this->company->toArray(),
            'user' => $this->user->toArray(),
            'profile' => $this->profile->toArray(),
            'extraData' => $this->extraData,
        ];
    }
}