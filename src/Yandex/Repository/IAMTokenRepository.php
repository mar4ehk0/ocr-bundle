<?php

namespace mar4ehk0\OCRBundle\Yandex\Repository;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use mar4ehk0\OCRBundle\Yandex\Entity\IAMToken;

/**
 * @method IAMToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method IAMToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method IAMToken[]    findAll()
 * @method IAMToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IAMTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IAMToken::class);
    }

    public function add(IAMToken $token): void
    {
        $this->getEntityManager()->persist($token);
    }

    public function remove(IAMToken $token): void
    {
        $this->getEntityManager()->remove($token);
    }

    public function getActiveToken(DateTimeImmutable $dateTime): IAMToken
    {
        return $this
            ->createQueryBuilder('t')
            ->andWhere('t.expired > :date')
            ->setParameter(':date', $dateTime)
            ->orderBy('t.expired', 'DESC')
            ->getQuery()
            ->getSingleResult();
    }
}
