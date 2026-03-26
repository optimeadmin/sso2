<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Server\Controller;

use Optime\Sso\Bundle\Server\Security\SecurityDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
#[Route('/sso/server')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly SecurityDataProvider $securityDataProvider,
    ) {
    }

    #[Route('/auth', name: 'optime_sso_server_auth', methods: 'POST')]
    public function auth(Request $request): Response
    {
        if (!$request->headers->has('sso-token')) {
            throw new AccessDeniedHttpException('sso-token header not found');
        }

        $jwt = $request->headers->get('sso-token');

        return $this->json($this->securityDataProvider->generate($jwt));
    }
}
