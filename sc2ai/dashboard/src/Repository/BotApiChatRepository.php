<?php

namespace App\Repository;

use App\Entity\BotApiChat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BotApiChat|null find($id, $lockMode = null, $lockVersion = null)
 * @method BotApiChat|null findOneBy(array $criteria, array $orderBy = null)
 * @method BotApiChat[]    findAll()
 * @method BotApiChat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BotApiChatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BotApiChat::class);
    }

    // /**
    //  * @return BotApiChat[] Returns an array of BotApiChat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BotApiChat
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
