<?php

namespace Optime\Sso\Bundle\Server;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SsoClientUrlGenerator
{
    public function __construct(
        private readonly Security           $security,
        private readonly SsoParamsGenerator $paramsGenerator,
    ) {
    }

    public function generate(string $clientCode, string $clientUrl, int $regenerateAfter = 0): string
    {
        $user = $this->security->getUser();
        if (!$user instanceof UserIdentifierAwareInterface) {
            throw new UnauthorizedHttpException('User must implement UserIdentifierAwareInterface');
        }

        $ssoData = $this->paramsGenerator->generate($clientCode, $user, $regenerateAfter);

        return $clientUrl . (str_contains($clientUrl, '?') ? '&' : '?') . http_build_query($ssoData);
    }
}