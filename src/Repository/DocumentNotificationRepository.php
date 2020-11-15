<?php

namespace App\Repository;

use App\Entity\DocumentNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DocumentNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentNotification[]    findAll()
 * @method DocumentNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentNotification::class);
    }

    // /**
    //  * @return DocumentNotification[] Returns an array of DocumentNotification objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DocumentNotification
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
