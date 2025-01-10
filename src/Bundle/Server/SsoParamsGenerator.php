<?php

namespace Optime\Sso\Bundle\Server;

use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SsoParamsGenerator
{
    public function __construct(
        private readonly JwtTokenGenerator $tokenGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function generate(
        string $clientCode,
        UserIdentifierAwareInterface $userIdentifierAware,
        int $regenerateAfter = 60,
    ): array {
        $token = $this->generateToken($clientCode, $userIdentifierAware, $regenerateAfter);

        if (!$token) {
            return [];
        }

        return [
            'sso-token' => $token,
            'sso-auth-url' => $this->urlGenerator->generate(
                'optime_sso_server_auth',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ];
    }
}