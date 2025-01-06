<?php

namespace Optime\Sso\Bundle\Server\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Optime\Sso\Component\Server\Token\TokenInterface;
use Optime\Sso\Component\Server\UserIdentifierAwareInterface;

#[Entity]
#[Table(name: 'optime_sso_server_user_token')]
class UserToken implements TokenInterface
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    private ?int $id = null;

    #[Column]
    private ?string $token = null;

    #[Column(type: 'string')]
    private null|string|int $userIdenfifier = null;

    #[Column(type: 'json')]
    private ?array $userData = null;

    #[Column]
    private ?string $refreshToken = null;

    #[Column]
    private ?\DateTimeImmutable $expirationAt = null;

    #[Column]
    private ?bool $isUserDataRead = false;

//    public static function fromUser(UserIdentifierAwareInterface $userIdentifierAware): self
//    {
//        $obj = new self();
//    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserIdenfifier(): string|int
    {
        return $this->userIdenfifier;
    }

    public function getUserData(): array
    {
        return $this->userData;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpirationAt(): \DateTimeImmutable
    {
        return $this->expirationAt;
    }

    public function isUserDataRead(): bool
    {
        return $this->isUserDataRead;
    }
}