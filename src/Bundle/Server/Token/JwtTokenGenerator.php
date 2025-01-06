<?php

namespace Optime\Sso\Bundle\Server\Token;

use Firebase\JWT\JWT;
use Optime\Sso\Bundle\Server\Token\ServerTokenGenerator;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;

class JwtTokenGenerator
{
    public function __construct(
        private readonly ServerTokenGenerator $tokenGenerator,
        private readonly string $privateKey,
    ) {
    }

    public function generate(string $clientCode, UserIdentifierAwareInterface $userIdentifierAware): string
    {
        $token = $this->tokenGenerator->generate($clientCode, $userIdentifierAware);

        return JWT::encode(
            [
                'token' => $token->getToken(),
                'exp' => time() + 10,
            ],
            $this->privateKey,
            'HS256'
        );
    }
}