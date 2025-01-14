<?php

namespace Optime\Sso\Bundle\Client\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class LocalLoginSsoExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getUrl(): string
    {
        $request = $this->requestStack->getMainRequest();
        $currentUrl = $request->getRequestUri();

        return $currentUrl;
    }
}