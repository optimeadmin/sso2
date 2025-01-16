<?php

namespace Optime\Sso\Bundle\Client;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocalServerChecker
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ?string $localConfiguredIp,
    ) {
    }

    public function isLocalServer(Request $request = null): bool
    {
        if (!$request) {
            if (!$request = $this->requestStack->getCurrentRequest()) {
                return false;
            }
        }

        $ip = $request->getClientIp();

        return in_array($ip, array_filter(['127.0.0.1', 'fe80::1', '::1', $this->localConfiguredIp]));
    }
}