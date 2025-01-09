<?php

namespace Optime\Sso\Bundle\Server\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Optime\Sso\Bundle\Server\Entity\UserToken;
use Optime\Sso\Bundle\Server\Token\ServerTokenGenerator;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class JwtTokenGenerator
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ServerTokenGenerator $tokenGenerator,
        private readonly string $privateKey,
        private readonly int $expirationSeconds,
    ) {
    }

    public function generate(
        string $clientCode,
        UserIdentifierAwareInterface $userIdentifierAware,
        int $regenerateAfter = 60,
    ): ?string {
        $sessionKey = sprintf(
            'sso_server.token.regeneration.%s.%s',
            $clientCode,
            $userIdentifierAware->getSsoIdentifier()
        );

        if ($value = $this->getSession()?->get($sessionKey)) {
            if ((int)$value >= time()) {
                // Si no ha expirado el tiempo para regenerar el token, retornamos null
                return null;
            }
        }

        $token = $this->tokenGenerator->generate($clientCode, $userIdentifierAware);

        $this->getSession()?->set($sessionKey, time() + $regenerateAfter);

        return JWT::encode(
            [
                'token' => $token->getToken(),
                'exp' => time() + $this->expirationSeconds,
            ],
            $this->privateKey,
            'HS256'
        );
    }

    public function decodeToken(string $encodedToken): string
    {
        return JWT::decode($encodedToken, new Key($this->privateKey, 'HS256'))->token;
    }

    private function getSession(): ?SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return null;
        }
    }
}