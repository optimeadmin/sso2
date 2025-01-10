<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Server\Controller;

use Optime\Sso\Bundle\Server\Security\SecurityDataProvider;
use Optime\Sso\Bundle\Server\SsoParamsGenerator;
use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Path('/sso/url')]
class UrlController extends AbstractController
{
    public function __construct(
        private readonly SecurityDataProvider $securityDataProvider,
        private readonly SsoParamsGenerator $paramsGenerator,
    ) {
    }

    #[Route('/redirect', name: 'optime_sso_server_generate_url', methods: 'GET')]
    public function redirectToClient(Request $request): Response
    {
        $client = $request->query->getString('client');
        $target = $request->query->getString('target');
        $regenerateAfter = $request->query->getInt('regenerateAfter');

        if (!$client) {
            throw new NotFoundHttpException('client query not found');
        }
        if (!$target) {
            throw new NotFoundHttpException('target url query not found');
        }

        $ssoData = $this->paramsGenerator->generate($client, $this->getUser(), $regenerateAfter);
        $url = $target . str_contains('?', $target) ? '&' : '?' . http_build_query($ssoData);

        return $this->redirect($target);
    }
}
