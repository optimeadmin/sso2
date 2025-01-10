<?php

namespace Optime\Sso\Bundle\Client\Log;

use Doctrine\ORM\EntityManagerInterface;
use Optime\Sso\Bundle\Client\Security\SsoData;
use Optime\Sso\Bundle\Client\Entity\SsoLoginError;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ResetInterface;

class LoginErrorLogger implements ResetInterface
{
    private ?SsoLoginError $lastLog = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ?LoggerInterface $logger,
    ) {
    }

    public function forServer(\Throwable $error, string $authToken, string $authUrl, ?array $ssoData = null): void
    {
        $log = new SsoLoginError();
        $log->error = $error->getMessage();
        $log->authToken = $authToken;
        $log->authUrl = $authUrl;
        $log->ssoData = $ssoData;
        $log->step = 'server_call';

        $this->persist($log, $error);
    }

    public function forClientAuth(\Throwable $error, ?SsoData $data, string $step): void
    {
        $log = new SsoLoginError();
        $log->error = $error->getMessage();
        $log->userIdentifier = $data?->getUserIdentifier();
        $log->ssoData = $data?->jsonSerialize();
        $log->step = $step;

        $this->persist($log, $error);
    }

    public function getLastLog(): ?SsoLoginError
    {
        return $this->lastLog;
    }

    public function reset(): void
    {
        $this->lastLog = null;
    }

    private function persist(SsoLoginError $log, \Throwable $ssoError): void
    {
        try {
            $this->entityManager->persist($log);
            $this->entityManager->flush();

            $this->lastLog = $log;
        } catch (\Throwable $exception) {
            $this->logger->critical($exception->getMessage(), [
                'app_error' => $exception,
                'sso_error' => $ssoError,
            ]);
        }
    }
}