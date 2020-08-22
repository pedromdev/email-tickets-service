<?php

namespace App\Repository;

use App\Entity\MessageReceived;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageReceived|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageReceived|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageReceived[]    findAll()
 * @method MessageReceived[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageReceivedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageReceived::class);
    }

    /**
     * @return MessageReceived[]
     */
    public function findMessagesFromTwoWeeksAgo()
    {
        $queryBuilder = $this->createQueryBuilder('m');

        return $queryBuilder->where($queryBuilder->expr()->gte('processed_at', new \DateTime('2 weeks ago')))
            ->getQuery()
            ->getResult();
    }
}
