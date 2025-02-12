<?php

namespace Optime\Sso\Bundle\Server\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Optime\Sso\Bundle\Server\Entity\UserToken;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class JwtTokenGenerator
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ServerTokenGenerator $tokenGenerator,
        private readonly string $privateKey,
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

        if ($regenerateAfter > 0 && $value = $this->getSession()?->get($sessionKey)) {
            if ((int)$value >= time()) {
                // Si no ha expirado el tiempo para regenerar el token, retornamos null
                return null;
            }
        }

        $token = $this->tokenGenerator->generate($clientCode, $userIdentifierAware);

        if ($regenerateAfter > 0) {
            $this->getSession()?->set($sessionKey, time() + $regenerateAfter);
        }

        return JWT::encode(
            [
                'token' => $token->getToken(),
                'clientCode' => $token->getClientCode(),
                'exp' => time() + 30,
            ],
            $this->privateKey,
            'HS256'
        );
    }

    public function generateApiTokens(UserToken $userToken): array
    {
        $identifier = $userToken->getUserIdentifier();
        $clientCode = $userToken->getClientCode();
        $tokenData = $userToken->getApiTokenData() ?? [];

        $token = JWT::encode([
            'userIdentifier' => $identifier,
            'clientCode' => $clientCode,
            'exp' => time() + (3600 * 4),
            'extraData' => $tokenData,
        ], $this->privateKey, 'HS256');

        $refreshToken = JWT::encode([
            'userIdentifier' => $identifier,
            'clientCode' => $clientCode,
            'exp' => time() + (3600 * 24),
        ], $this->privateKey, 'HS256');

        return [$token, $refreshToken];
    }

    public function decodeToken(string $encodedToken): array
    {
        $data = JWT::decode($encodedToken, new Key($this->privateKey, 'HS256'));

        return [$data->token, $data->clientCode];
    }

    public function decodeApiToken(string $encodedToken): array
    {
        $data = JWT::decode($encodedToken, new Key($this->privateKey, 'HS256'));

        return [$data->userIdentifier, (array)$data->extraData, $data->clientCode];
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