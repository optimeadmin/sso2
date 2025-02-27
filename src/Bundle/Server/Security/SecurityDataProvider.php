<?php

namespace Optime\Sso\Bundle\Server\Security;

use Optime\Sso\Bundle\Server\Event\StartAuthEvent;
use Optime\Sso\Bundle\Server\Repository\UserTokenRepository;
use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityDataProvider
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly JwtTokenGenerator $tokenGenerator,
        private readonly UserTokenRepository $tokenRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $serverCode,
    ) {
    }

    public function generate(string $jwt): array
    {
        try {
            [$token, $clientCode] = $this->tokenGenerator->decodeToken($jwt);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }

        $userToken = $this->tokenRepository->getValidToken($token);

        if (!$userToken) {
            throw new AccessDeniedHttpException('Token not found');
        }

        $this->tokenRepository->clearTokens($userToken);

        $this->dispatcher->dispatch(new StartAuthEvent($userToken));

        return [
            'serverCode' => $this->serverCode,
            'userData' => $userToken->getUserData(),
            'apiTokens' => $this->tokenGenerator->generateApiTokens($userToken),
            'serverUrl' => $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost(),
            'refreshTokenUrl' => $this->urlGenerator->generate(
                'optime_sso_server_refresh_token',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ];
    }

    public function regenerateTokens(string $refreshToken): array
    {
        try {
            [$identifier, $extraData, $clientCode] = $this->tokenGenerator->decodeApiToken($refreshToken);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }

        return $this->tokenGenerator->doBuildApiTokens($identifier, $clientCode, $extraData);
    }
}