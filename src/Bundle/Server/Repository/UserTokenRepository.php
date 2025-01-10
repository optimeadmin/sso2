<?php

namespace Optime\Sso\Bundle\Server\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Optime\Sso\Bundle\Server\Entity\UserToken;

class UserTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToken::class);
    }

    public function saveNewToken(UserToken $token): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($token);
        $entityManager->flush();
    }

    public function getValidToken(string $token): ?UserToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function clearTokens(UserToken $token): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->delete(UserToken::class, 't')
            ->andWhere('t.clientCode = :client_code')
            ->andWhere('t.userIdentifier = :user_identifier')
            ->andWhere('(t.createdAt < :created_at OR t.token = :token)')
            ->setParameter('client_code', $token->getClientCode())
            ->setParameter('user_identifier', $token->getUserIdentifier())
            ->setParameter('token', $token->getToken())
            ->setParameter('created_at', new \DateTimeImmutable('-5 minutes'))
            ->getQuery()
            ->execute();
    }
}