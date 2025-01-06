<?php

namespace Optime\Sso\Bundle\Server\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Optime\Sso\Bundle\Server\Entity\UserToken;
use Optime\Sso\Bundle\Server\Token\ServerTokenGenerator;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;

class JwtTokenGenerator
{
    public function __construct(
        private readonly ServerTokenGenerator $tokenGenerator,
        private readonly string $privateKey,
        private readonly string $expirationSeconds,
    ) {
    }

    public function generate(string $clientCode, UserIdentifierAwareInterface $userIdentifierAware): string
    {
        $token = $this->tokenGenerator->generate($clientCode, $userIdentifierAware);

        return JWT::encode(
            [
                'token' => $token->getToken(),
                'exp' => time() + $this->expirationSeconds,
            ],
            $this->privateKey,
            'HS256'
        );
    }

    public function generateRefresh(UserToken $token): string
    {
        return JWT::encode(
            [
                'refresh' => $token->getRefreshToken(),
                'exp' => time() + (3600 * 24),
            ],
            $this->privateKey,
            'HS256'
        );
    }

    public function decodeToken(string $encodedToken): string
    {
        return JWT::decode($encodedToken, new Key($this->privateKey, 'HS256'))->token;
    }
}