<?php

namespace Optime\Sso\Bundle\Server\Twig;

use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;
use Twig\Extension\RuntimeExtensionInterface;

class SsoTokenExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly JwtTokenGenerator $tokenGenerator)
    {
    }

    public function generateToken(
        string $clientCode,
        UserIdentifierAwareInterface $userIdentifierAware,
        int $regenerateAfter = 60,
    ): ?string {
        return $this->tokenGenerator->generate($clientCode, $userIdentifierAware, $regenerateAfter);
    }
}