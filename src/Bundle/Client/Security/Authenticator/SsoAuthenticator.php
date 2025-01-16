<?php

namespace Optime\Sso\Bundle\Client\Security\Authenticator;

use Optime\SimpleSsoClientBundle\Event\SimpleSsoLoginEvent;
use Optime\SimpleSsoClientBundle\Security\TokenAttributes;
use Optime\Sso\Bundle\Client\Event\LoginSuccessEvent;
use Optime\Sso\Bundle\Client\Factory\UserFactoryInterface;
use Optime\Sso\Bundle\Client\Factory\UserFactoryResult;
use Optime\Sso\Bundle\Client\LocalServerChecker;
use Optime\Sso\Bundle\Client\Log\LoginErrorLogger;
use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\Bundle\Client\Security\SsoDataProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
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
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SsoAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly SsoDataProvider $ssoDataProvider,
        private readonly SsoEntryPoint $entryPoint,
        private readonly UserFactoryInterface $userFactory,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LocalServerChecker $localServerChecker,
        private readonly LoginErrorLogger $errorLogger,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        if ($request->query->has('sso-token') && $request->query->has('sso-auth-url')) {
            return true;
        }

        if ($request->query->has('sso-local-token') && $this->localServerChecker->isLocalServer($request)) {
            return true;
        }

        return false;
    }

    /**
     * @throws \Throwable
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function authenticate(Request $request): Passport
    {
        if ($request->query->has('sso-local-token')) {
            $isLocal = true;
            $ssoData = $this->getLocalSsoData($request);
            $this->errorLogger->setAsLocalLogin();
        } else {
            $isLocal = false;
            $ssoData = $this->getSsoData($request);
        }

        try {
            $userData = $this->userFactory->create($ssoData);
        } catch (\Throwable $e) {
            $this->errorLogger->forClientAuth($e, $ssoData, 'user_factory');

            throw new AuthenticationException('Authentication error', $e->getCode(), $e);
        }

        $passport = new SelfValidatingPassport(new UserBadge('', function () use ($userData) {
            return $userData->getUser();
        }));

        $passport->setAttribute('user_data', $userData);
        $passport->setAttribute('is_local', $isLocal);

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        /** @var UserFactoryResult $userData */
        $userData = $passport->getAttribute('user_data');
        $roles = $userData->getRoles();

        $token = new PostAuthenticationToken($passport->getUser(), $firewallName, $roles);
        $token->setAttributes($userData->getAttributes());

        if ($passport->getAttribute('is_local')) {
            $token->setAttribute('is_local', true);
        }

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

        if ($this->errorLogger->isLocalLogin()) {
            throw $exception;
        }

        if ($this->tokenStorage->getToken()) {
            $this->tokenStorage->setToken(null);
        }

        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if ($authException && $this->errorLogger->isLocalLogin()) {
            throw  $authException;
        }

        return $this->entryPoint->start($request, $authException);
    }

    /**
     * @throws \Throwable
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getSsoData(Request $request): SsoData
    {
        $authToken = $request->query->get('sso-token');
        $authUrl = $request->query->get('sso-auth-url');

        try {
            return $this->ssoDataProvider->byToken($authToken, $authUrl);
        } catch (\Throwable $error) {
            $this->errorLogger->forServer($error, $authToken, $authUrl, $this->ssoDataProvider->getLastSsoData());

            throw $error;
        }
    }

    /**
     * @throws \Throwable
     */
    private function getLocalSsoData(Request $request): SsoData
    {
        $token = $request->query->get('sso-local-token');

        try {
            return $this->ssoDataProvider->byLocalToken($token);
        } catch (\Throwable $error) {
            $this->errorLogger->forServer($error, $token, '__LOCAL__', $this->ssoDataProvider->getLastSsoData());

            throw $error;
        }
    }
}