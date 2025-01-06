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

#[Entity(repositoryClass: UserTokenRepository::class)]
#[Table(name: 'optime_sso_server_user_token')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class UserToken
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    private ?int $id = null;

    #[Column]
    private ?string $clientCode = null;

    #[Column]
    private ?string $token = null;

    #[Column(type: 'string')]
    private null|string|int $userIdentifier = null;

    #[Column(type: 'json')]
    private ?array $userData = null;

    #[Column]
    private ?string $refreshToken = null;

    #[Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Column]
    private ?bool $active = true;

    public static function fromUser(
        string $clientCode,
        UserIdentifierAwareInterface $userIdentifierAware,
        CompanyUserData $companyUserData,
    ): self {
        $obj = new self();
        $obj->clientCode = $clientCode;
        $obj->token = Uuid::v4();
        $obj->refreshToken = Uuid::v4();
        $obj->userIdentifier = $userIdentifierAware->getSsoIdentifier();
        $obj->userData = $companyUserData->toArray();
        $obj->createdAt = new \DateTimeImmutable();

        return $obj;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientCode(): string
    {
        return $this->clientCode;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserIdentifier(): string|int
    {
        return $this->userIdentifier;
    }

    public function getUserData(): array
    {
        return $this->userData;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}