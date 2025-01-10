<?php

namespace Optime\Sso\Bundle\Client\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

class LoginSuccessEvent extends Event
{
    private ?Response $response = null;

    public function __construct(
        public readonly Request $request,
        public readonly TokenInterface $token,
        public readonly string $firewallName,
    ) {
    }

    public function hasResponse(): bool
    {
        return null !== $this->getResponse();
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
        $this->stopPropagation();
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}