<?php

namespace Optime\Sso\Bundle\Client\Security;

use Optime\Sso\Bundle\Client\Factory\UserFactoryInterface;
use Optime\Sso\User\CompanyUserData;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SsoDataProvider
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly RequestStack $requestStack,
        private readonly UserFactoryInterface $userFactory,
    ) {
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function byToken(string $token, string $url): SsoData
    {
        $response = $this->httpClient->request(
            'POST',
            $url,
            [
                'headers' => ['sso-token' => $token],
                'verify_peer' => !$this->isLocalServer(),
            ]
        );

        if ($response->getStatusCode() >= 400) {
            throw new AuthenticationException(strip_tags($response->getContent(false)));
        }

        $data = $response->toArray(false) ?? [];

        if (!isset($data['serverCode'])) {
            throw new AuthenticationException('serverCode is required in auth server response');
        }

        if (!isset($data['userData'])) {
            throw new AuthenticationException('userData is required in auth server response');
        }

        return new SsoData(
            $data['serverCode'],
            CompanyUserData::fromArray($this->resolveData($data['userData'])),
        );
    }

    private function resolveData(array $serverData): array
    {
        $companyResolver = new OptionsResolver();
        $userResolver = new OptionsResolver();
        $profileResolver = new OptionsResolver();
        $extraDataResolver = new OptionsResolver();

        $companyResolver->setIgnoreUndefined();
        $userResolver->setIgnoreUndefined();
        $profileResolver->setIgnoreUndefined();
        $extraDataResolver->setIgnoreUndefined();

        $this->userFactory->configureOptions($companyResolver, $userResolver, $profileResolver, $extraDataResolver);

        return [
            'company' => $this->resolveKey($companyResolver, $serverData['company'] ?? []),
            'user' => $this->resolveKey($userResolver, $serverData['user'] ?? []),
            'profile' => $this->resolveKey($profileResolver, $serverData['profile'] ?? []),
            'extraData' => $extraDataResolver->resolve($data['extraData'] ?? []),
        ];
    }

    private function resolveKey(OptionsResolver $resolver, array $keyData): array
    {
        return [
            'base' => $keyData['base'] ?? [],
            'extra' => $resolver->resolve($keyData['extra'] ?? []),
        ];
    }

    private function isLocalServer(): bool
    {
        $ip = $this->requestStack->getCurrentRequest()->getClientIp();

        return in_array($ip, ['127.0.0.1', 'fe80::1', '::1']);
    }
}