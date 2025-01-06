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

        $entityManager
            ->createQueryBuilder()
            ->update(UserToken::class, 't')
            ->set('t.active', false)
            ->where('t.active = true')
            ->andWhere('t.clientCode = :client_code')
            ->andWhere('t.userIdentifier = :user_identifier')
            ->setParameter('client_code', $token->getClientCode())
            ->setParameter('user_identifier', $token->getUserIdentifier())
            ->getQuery()
            ->execute();

        $entityManager->persist($token);
        $entityManager->flush();
    }
}