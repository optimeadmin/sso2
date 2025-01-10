<?php

namespace Optime\Sso\Bundle\Server\Twig;

use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class SsoTokenExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly JwtTokenGenerator $tokenGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function generateToken(
        string $clientCode,
        UserIdentifierAwareInterface $userIdentifierAware,
        int $regenerateAfter = 60,
    ): ?string {
        return $this->tokenGenerator->generate($clientCode, $userIdentifierAware, $regenerateAfter);
    }

    public function generateSsoParams(
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