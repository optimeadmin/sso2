<?php

namespace Optime\Sso\Bundle\Server\Token;

use Optime\Sso\Bundle\Server\Entity\UserToken;
use Optime\Sso\Bundle\Server\Repository\UserTokenRepository;
use Optime\Sso\Bundle\Server\Token\User\UserDataFactoryInterface;
use Optime\Sso\Bundle\Server\UserIdentifierAwareInterface;

class ServerTokenGenerator
{
    public function __construct(
        private readonly UserDataFactoryInterface $dataFactory,
        private readonly UserTokenRepository $repository,
    ) {
    }

    public function generate(string $clientCode, UserIdentifierAwareInterface $userIdentifierAware): UserToken
    {
        $userData = $this->dataFactory->create($userIdentifierAware);
        $token = UserToken::fromUser($clientCode, $userIdentifierAware, $userData);

        $this->repository->saveNewToken($token);

        return $token;
    }
}