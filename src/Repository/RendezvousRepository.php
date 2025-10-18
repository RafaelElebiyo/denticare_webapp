<?php

namespace App\Repository;

use App\Entity\Rendezvous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rendezvous>
 */
class RendezvousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rendezvous::class);
    }


        public function findUserRVActif($user, $date): array
        {
            return $this->createQueryBuilder('r')
            ->where('r.date LIKE :date')
            ->andWhere('r.patient = :patient')
            ->setParameter('date', '%' . $date . '%')
            ->setParameter('patient', $user)
            ->getQuery()
            ->getResult();
        }

        
        public function findConsultaByHoy($date, $dentiste):array
        {
            return $this->createQueryBuilder('r')
                ->where('r.actif = :actif')
                ->andWhere('r.dentiste = :dentiste')
                ->andWhere('r.date LIKE :date')
                ->setParameter('actif', true)
                ->setParameter('dentiste', $dentiste)
                ->setParameter('date', '%' . $date . '%')
                ->orderBy('r.date', 'ASC')
                ->getQuery()
                ->getResult();
        }

        public function disablePastRendezvouses()
            {
                $hoy = new \DateTime(); // Obtener la fecha de hoy como objeto DateTime

                // Crear una consulta de actualización con Query Builder
                $queryBuilder = $this->createQueryBuilder('r');
                $queryBuilder->update('App\Entity\Rendezvous', 'r')
                    ->set('r.actif', ':actif')
                    ->set('r.observation', ':observation')
                    ->where('r.date < :hoy')
                    ->setParameter('actif', false)
                    ->setParameter('observation', 'Rendez-vous expiré')
                    ->setParameter('hoy', $hoy);

                // Ejecutar la consulta
                $queryBuilder->getQuery()->execute();
            }


    //    /**
    //     * @return Rendezvous[] Returns an array of Rendezvous objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Rendezvous
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
