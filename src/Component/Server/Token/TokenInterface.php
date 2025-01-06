<?php
/**
 * (c) Optime Consulting
 */

namespace Optime\Sso\Component\Server\Token;

interface TokenInterface
{
    public function getToken(): string;

    public function isUserDataRead(): bool;

    public function getUserIdentifier(): string|int;

    public function getUserData(): array;

    public function getRefreshToken(): string;

    public function getExpirationAt(): \DateTimeInterface;
}