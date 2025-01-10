<?php

namespace Optime\Sso\Bundle\Client\Security\Authenticator;

use Optime\SimpleSsoClientBundle\Event\SimpleSsoLoginEvent;
use Optime\SimpleSsoClientBundle\Security\TokenAttributes;
use Optime\Sso\Bundle\Client\Event\LoginSuccessEvent;
use Optime\Sso\Bundle\Client\Factory\UserFactoryInterface;
use Optime\Sso\Bundle\Client\Factory\UserFactoryResult;
use Optime\Sso\Bundle\Client\Log\LoginErrorLogger;
use Optime\Sso\Bundle\Client\Security\SsoDataProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class SsoAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly SsoDataProvider $ssoDataProvider,
        private readonly SsoEntryPoint $entryPoint,
        private readonly UserFactoryInterface $userFactory,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoginErrorLogger $errorLogger,
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

        try {
            $ssoData = $this->ssoDataProvider->byToken($authToken, $authUrl);
        } catch (\Throwable $error) {
            $this->errorLogger->forServer($error, $authToken, $authUrl, $this->ssoDataProvider->getLastSsoData());

            throw $error;
        }

        try {
            $userData = $this->userFactory->create($ssoData);
        } catch (\Throwable $e) {
            $this->errorLogger->forClientAuth($e, $ssoData, 'user_factory');

            throw new AuthenticationException('Authentication error', $e->getCode(), $e);
        }

        $passport = new SelfValidatingPassport(new UserBadge($authToken, function () use ($userData) {
            return $userData->getUser();
        }));

        $passport->setAttribute('user_data', $userData);

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        /** @var UserFactoryResult $userData */
        $userData = $passport->getAttribute('user_data');
        $roles = $userData->getRoles();

        $token = new PostAuthenticationToken($passport->getUser(), $firewallName, $roles);
        $token->setAttributes($userData->getAttributes());

        return $token;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->errorLogger->reset();

        $event = new LoginSuccessEvent($request, $token, $firewallName);
        $this->dispatcher->dispatch($event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        $request = $request->duplicate();
        $request->query->remove('sso-token');
        $request->query->remove('sso-auth-url');
        $request->server->set('QUERY_STRING', http_build_query($request->query->all()));

        return new RedirectResponse($request->getUri(), 302);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if (!$this->errorLogger->getLastLog()) {
            $this->errorLogger->forClientAuth($exception, null, 'auth_failure');
        }

        if ($this->tokenStorage->getToken()) {
            $this->tokenStorage->setToken(null);
        }

        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->entryPoint->start($request, $authException);
    }
}