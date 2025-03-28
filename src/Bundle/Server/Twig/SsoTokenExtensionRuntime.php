<?php

namespace Optime\Sso\Bundle\Server\Twig;

use Optime\Sso\Bundle\Server\SsoParamsGenerator;
use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class SsoTokenExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly JwtTokenGenerator $tokenGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SsoParamsGenerator $ssoParamsGenerator,
        private readonly Security $security,
    ) {
    }

    public function generateToken(
        string $clientCode,
        UserIdentifierAwareInterface $userIdentifierAware,
        int $regenerateAfter = 10,
    ): ?string {
        return $this->tokenGenerator->generate($clientCode, $userIdentifierAware, $regenerateAfter);
    }

    public function generateSsoParams(
        string $clientCode,
        UserIdentifierAwareInterface $userIdentifierAware,
        int $regenerateAfter = 10,
    ): array {
        return $this->ssoParamsGenerator->generate($clientCode, $userIdentifierAware, $regenerateAfter);
    }

    public function generateSsoUrl(string $clientCode, string $url, int $regenerateAfter = 0): string
    {
        return $this->urlGenerator->generate('optime_sso_server_generate_url', [
            'client' => $clientCode,
            'target' => $url,
            'regenerateAfter' => $regenerateAfter,
        ]);
    }

    public function generateIframeSsoUrl(string $clientCode, string $externalUrl, int $regenerateAfter = 0): string
    {
        $ssoData = $this->generateSsoParams($clientCode, $this->security->getUser(), $regenerateAfter);

        return $externalUrl.(str_contains($externalUrl, '?') ? '&' : '?').http_build_query($ssoData);
    }
}