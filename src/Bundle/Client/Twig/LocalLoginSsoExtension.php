<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Client\Twig;

use Optime\Sso\Bundle\Client\Twig\Runtime\LocalLoginSsoExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class LocalLoginSsoExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sso_local_login_start_url', [LocalLoginSsoExtensionRuntime::class, 'getUrl']),
        ];
    }
}
