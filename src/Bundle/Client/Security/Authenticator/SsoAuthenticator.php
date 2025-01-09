<?php

namespace Optime\Sso\Bundle\Client\Security\Authenticator;

use Optime\Sso\Bundle\Client\Security\User\PreAuthenticatedUser;
use Optime\Sso\Bundle\Client\Security\User\Provider\SsoUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class SsoAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly SsoUserProvider $ssoUserProvider,
        private readonly SsoEntryPoint $entryPoint,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->query->has('sso-token') && $request->query->has('sso-auth-url');
    }

    public function authenticate(Request $request): Passport
    {
        $authToken = $request->query->get('sso-token');
        $authUrl = $request->query->get('sso-auth-url');

        return new SelfValidatingPassport(new UserBadge($authToken, function ($token) use ($authUrl) {
            return $this->ssoUserProvider->getUserByToken($token, $authUrl);
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        dd($token);
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        dd($exception);
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->entryPoint->start($request, $authException);
    }
}