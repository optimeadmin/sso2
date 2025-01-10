<?php

namespace Optime\Sso\Bundle\Server\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Optime\Sso\Bundle\Server\Repository\UserTokenRepository;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;
use Optime\Sso\User\CompanyUserData;
use Symfony\Component\Uid\Uuid;

#[Table(name: 'optime_sso_client_login_error')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class SsoLoginError
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    private ?int $id = null;

    #[Column(type: 'string')]
    public ?string $step = null;

    #[Column(type: 'text')]
    public ?string $error = null;

    #[Column(type: 'text')]
    public ?string $authToken = null;

    #[Column(type: 'string')]
    public ?string $authUrl = null;

    #[Column(type: 'string')]
    public ?string $userIdentifier = null;

    #[Column(type: 'json')]
    public ?array $ssoData = null;

    #[Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}