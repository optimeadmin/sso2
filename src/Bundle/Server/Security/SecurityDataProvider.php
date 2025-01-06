<?php

namespace Optime\Sso\Bundle\Server\Security;

use Optime\Sso\Bundle\Server\Repository\UserTokenRepository;
use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SecurityDataProvider
{
    public function __construct(
        private readonly JwtTokenGenerator $tokenGenerator,
        private readonly UserTokenRepository $tokenRepository,
        private readonly string $serverCode,
    ) {
    }

    public function generate(string $jwt): array
    {
        try {
            $token = $this->tokenGenerator->decodeToken($jwt);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }

        $userToken = $this->tokenRepository->getValidToken($token);

        if (!$userToken) {
            throw new AccessDeniedHttpException('Token not found');
        }

        return [
            'serverCode' => $this->serverCode,
            'userData' => $userToken->getUserData(),
            'refreshToken' => $this->tokenGenerator->generateRefresh($userToken),
        ];
    }
}