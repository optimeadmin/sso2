<?php

namespace Optime\Sso\Bundle\Client\DataCollector;

use Optime\Sso\Bundle\Client\LocalServerChecker;
use Optime\Sso\Bundle\Client\Token\LocalTokenGenerator;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SsoDataCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly Security $security,
        private readonly LocalTokenGenerator $tokenGenerator,
        private readonly LocalServerChecker $localServerChecker,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        if ($this->security->getUser() || !$this->localServerChecker->isLocalServer($request)) {
            $this->data = ['show' => false];

            return;
        }

        $url = $request->getRequestUri();
        $token = $this->tokenGenerator->generate();
        $url .= (str_contains($url, '?') ? '&' : '?').sprintf('sso-local-token=%s', $token);

        $this->data = [
            'url' => $url,
            'show' => true,
        ];
    }

    public function isShow(): bool
    {
        return $this->data['show'] ?? false;
    }

    public function getUrl(): string
    {
        return $this->data['url'] ?? '';
    }

    public static function getTemplate(): ?string
    {
        return '@OptimeSsoClient/collector/sso.html.twig';
    }
}