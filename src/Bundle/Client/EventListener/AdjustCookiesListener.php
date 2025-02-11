<?php

namespace Optime\Sso\Bundle\Client\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AdjustCookiesListener
{
    public function __construct(
        private readonly bool $partitionedCookie,
    ) {
    }

    #[AsEventListener(priority: -10000)]
    public function onResponse(ResponseEvent $event): void
    {
        if (!$this->partitionedCookie) {
            return;
        }
        $response = $event->getResponse();
        $headers = $response->headers;
        $cookies = $headers->getCookies();

        foreach ($cookies as $cookie) {
            $headers->setCookie($cookie->withPartitioned());
        }
    }
}