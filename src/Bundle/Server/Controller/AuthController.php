<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Server\Controller;

use Optime\Sso\Bundle\Server\Security\SecurityDataProvider;
use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(private readonly SecurityDataProvider $securityDataProvider)
    {
    }

    #[Route('/auth')]
    public function auth(Request $request, JwtTokenGenerator $tokenGenerator): Response
    {
        $jwt = $request->query->get('token');

        return $this->json($this->securityDataProvider->generate($jwt));
    }
}
