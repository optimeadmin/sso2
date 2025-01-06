<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Server\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SsoTokenExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('generate_sso_token', [SsoTokenExtensionRuntime::class, 'generateToken']),
        ];
    }
}
