<?php

namespace App\Repository;

use App\Entity\ProduitRemuneration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProduitRemuneration|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProduitRemuneration|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProduitRemuneration[]    findAll()
 * @method ProduitRemuneration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRemunerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitRemuneration::class);
    }

    // /**
    //  * @return ProduitRemuneration[] Returns an array of ProduitRemuneration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProduitRemuneration
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
