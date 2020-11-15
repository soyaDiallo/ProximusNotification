<?php

namespace App\Repository;

use App\Entity\Offre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Offre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offre[]    findAll()
 * @method Offre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    // /**
    //  * @return Offre[] Returns an array of Offre objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Offre
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getAllVentes()
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.dateSignature IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function getAllOffres()
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.dateSignature IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function getVentesByAgent($agent)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.dateSignature IS NOT NULL')
            ->andWhere('o.agent = :val')
            ->setParameter('val', $agent)
            ->getQuery()
            ->getResult();
    }

    public function getOffresByAgent($agent)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.dateSignature IS NULL')
            ->andWhere('o.agent = :val')
            ->setParameter('val', $agent)
            ->getQuery()
            ->getResult();
    }
}
