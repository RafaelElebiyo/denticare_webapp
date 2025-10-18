<?php

namespace App\Repository;

use App\Entity\Temoinage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Temoinage>
 */
class TemoinageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Temoinage::class);
    }
        
        public function findByRandom($limit): array
        { 
            $total =(int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

            $offset = $limit >= $total ? 0 : rand(0, $total - $limit);

            $qb = $this->createQueryBuilder('t')
                ->andWhere('t.actif = :actif ')
                ->setParameter('actif', true)
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            return $qb->getQuery()->getResult();
        }

    //    /**
    //     * @return Temoinage[] Returns an array of Temoinage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Temoinage
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
