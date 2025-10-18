<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    
        /**
         * @return Utilisateur[] Returns an array of Utilisateur objects
         */
        public function findActifDentistesAsc($page,$limit): array
        {
            return $this->createQueryBuilder('u')
                ->where('u.roles LIKE :role')
                ->andWhere('u.actif = :actif')
                ->setParameter('role', '%ROLE_DENTISTE%')
                ->setParameter('actif',true)
                ->orderBy('u.dde', 'DESC')
                ->setMaxResults($limit)
                ->setFirstResult(($page-1)*$limit)
                ->getQuery()
                ->getResult()
            ;
        }
        public function countByRoleAndActif()
        {
            return $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.roles LIKE :role')
                ->andWhere('u.actif = :actif')
                ->setParameter('role', '%ROLE_DENTISTE%')
                ->setParameter('actif', true)
                ->getQuery()
                ->getSingleScalarResult();
        }


        /**
         * @return Utilisateur[] Returns an array of Utilisateur objects
         */
        public function findByRoleActifAsc($role): array
        {
            return $this->createQueryBuilder('u')
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%'.$role.'%')
                ->orderBy('u.dde', 'ASC')
                ->orderBy('u.actif', 'DESC')
                ->setMaxResults(20)
                ->getQuery()
                ->getResult()
            ;
        }

        /**
         * @return Utilisateur[] Returns an array of Utilisateur objects
         */
        public function findByRoleVilleActifAsc($role, $ville): array
        {
            return $this->createQueryBuilder('u')
                ->where('u.roles LIKE :role')
                ->andWhere('u.ville = :ville')
                ->andWhere('u.actif = :actif')
                ->setParameter('role', '%'.$role.'%')
                ->setParameter('actif',true)
                ->setParameter('ville', $ville)
                ->orderBy('u.dde', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }

    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Utilisateur
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
