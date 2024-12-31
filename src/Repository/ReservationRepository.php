<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\StatutReservation;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findBetween(\DateTime $start, \DateTime $end): mixed
    {
        $debut = $start->format('Y-m-d');
        $fin = $end->format('Y-m-d');
        return  $this->createQueryBuilder('r')
            ->andWhere('r.date_fin >= :debut')
            ->orWhere('r.date_debut <= :fin')
            ->setParameter('debut', value: $debut)
            ->setParameter('fin', value: $fin)
            ->orderBy('r.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLastByNigend($nigend)
    {
        $query = $this->createQueryBuilder('r')
            ->andWhere('r.user = :nigend')
            ->andWhere('r.createdAt = :date')
            ->setParameter('nigend', $nigend)
            ->orderBy('r.createdAt', 'DESC');

        $date = $this->createQueryBuilder('r')
            ->select($query->expr()->max('r.createdAt'))->getQuery()->getResult();

        return $query
            ->setParameter(':date', $date)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByNigend($nigend)
    {
        $resas = $this->createQueryBuilder('r')
            ->andWhere('r.user = :nigend')
            ->setParameter('nigend', $nigend)
            ->orderBy('r.date_debut', 'ASC')
            ->getQuery()
            ->getResult();

        $em = $this->getEntityManager();

        $statut_termine = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Terminée']);

        $statut_en_cours = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'En cours']);

        $now = new \DateTime('now');
        foreach ($resas as $resa) {
            $statut = $resa->getStatut()->getCode();
            if ($statut === 'Terminée')
                continue;

            $date_debut = new \DateTime($resa->getDateDebut()->format('Y-m-d') . ' ' . $resa->getHeureDebut() . ':00');
            $date_fin = new \DateTime($resa->getDateFin()->format('Y-m-d') . ' ' . $resa->getHeureFin() . ':00');
            if ($date_fin->format('U') < $now->format('U')) {
                $resa->setStatut($statut_termine);
                $em->persist($resa);
                $em->flush();
            } else if ($date_debut->format('U') < $now->format('U') && $date_fin->format('U') > $now->format('U')) {
                if ($statut !== 'En attente') {
                    $resa->setStatut($statut_en_cours);
                    $em->persist($resa);
                    $em->flush();
                }
            }
        }

        return $resas;
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
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

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
