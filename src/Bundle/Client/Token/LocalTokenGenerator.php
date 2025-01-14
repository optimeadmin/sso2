<?php

namespace Optime\Sso\Bundle\Client\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LocalTokenGenerator
{
    public function __construct(
        private readonly string $privateKey,
    ) {
    }

    public function generate(): ?string
    {
        return JWT::encode(
            [
                'token' => '__LOCAL__',
                'exp' => time() + 60,
            ],
            $this->privateKey,
            'HS256'
        );
    }

    public function decodeToken(string $encodedToken): mixed
    {
        return JWT::decode($encodedToken, new Key($this->privateKey, 'HS256'));
    }
}