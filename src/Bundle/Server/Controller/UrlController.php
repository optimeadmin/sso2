<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Server\Controller;

use Optime\Sso\Bundle\Server\SsoClientUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED')]
#[Route('/sso/url')]
class UrlController extends AbstractController
{
    public function __construct(
        private readonly SsoClientUrlGenerator $clientUrlGenerator,
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

        $url = $this->clientUrlGenerator->generate($client, $target, $regenerateAfter);

        return $this->redirect($url);
    }
}