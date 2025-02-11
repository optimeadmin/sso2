<?php

namespace Optime\Sso\Bundle\Server\Security\Authenticator;

use Optime\Sso\Bundle\Server\Security\SsoApiUserProviderInterface;
use Optime\Sso\Bundle\Server\Token\JwtTokenGenerator;
use Optime\Sso\Bundle\Server\Token\User\UserDataFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class SsoApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly JwtTokenGenerator $tokenGenerator,
        private readonly UserDataFactoryInterface $userDataFactory,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('sso-api-auth-token');
    }

    public function authenticate(Request $request): Passport
    {
        $jwt = $request->headers->get('sso-api-auth-token');
        $tokenDSata = $this->tokenGenerator->decodeApiToken($jwt);
        $identifier = $tokenDSata[0];

        if ($this->userDataFactory instanceof SsoApiUserProviderInterface) {
            $userLoader = fn($identifier) => $this->userDataFactory->loadUserFromSsoApiId($identifier);
        } else {
            $userLoader = null;
        }

        return new Passport(new UserBadge($identifier, $userLoader), new CustomCredentials(fn() => true, null));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}