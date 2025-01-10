<?php

namespace Optime\Sso\Bundle\Client\Entity;

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

#[Entity]
#[Table(name: 'optime_sso_client_login_error')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\HasLifecycleCallbacks]
class SsoLoginError
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    private ?int $id = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $userIdentifier = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $step = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $error = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $authToken = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $authUrl = null;

    #[Column(type: 'json', nullable: true)]
    public ?array $ssoData = null;

    #[Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function cleanError(): void
    {
        if ($this->error && strlen($this->error) > 1000) {
            $this->error = strip_tags($this->error);
        }
    }
}