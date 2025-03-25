<?php
/**
 *
 */

declare(strict_types=1);

namespace Optime\Sso\Bundle\Client\Security\Authenticator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Twig\Environment;

class SsoEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if ('json' === $request->getContentTypeFormat()) {
            $response = new JsonResponse('Unauthorized');
        } else {
            $response = new Response($this->twig->render('@OptimeSsoClient/unauthorized.html.twig', [
                'exception' => $authException,
            ]));
        }

        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        $response->headers->set('X-Error-Message', $authException->getMessage());

        return $response;
    }
}