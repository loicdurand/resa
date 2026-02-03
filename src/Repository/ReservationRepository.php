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

    public function findBetween(int $departement, \DateTime $start, \DateTime $end): mixed
    {
        $debut = $start->format('Y-m-d');
        $fin = $end->format('Y-m-d');
        return  $this->createQueryBuilder('r')
            ->andWhere('r.date_fin >= :debut', 'r.date_debut <= :fin')
            ->andWhere('r.statut != 5')
            ->andWhere('v.departement = :dept')
            ->innerJoin('r.vehicule', 'v')
            // ->orWhere('r.date_debut <= :fin')
            ->setParameter('dept', $departement)
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
            ->orWhere('r.demandeur = :nigend')
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
            ->orWhere('r.demandeur = :nigend')
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

    public function findByNigends($nigends)
    {
        $resas = $this->createQueryBuilder('r')
            ->andWhere('r.user IN (:nigends)')
            ->orWhere('r.demandeur IN (:nigends)')
            ->setParameter('nigends', $nigends)
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

    public function findAllAfterNow(int $departement): mixed
    {
        $debut = (new \Datetime('now'))->format('Y-m-d');
        return  $this->createQueryBuilder('r')
            ->andWhere('r.date_fin >= :debut')
            ->andWhere('v.departement= :dept')
            ->innerJoin('r.vehicule', 'v')
            // ->orWhere('r.date_debut <= :fin')
            ->setParameter('dept', $departement)
            ->setParameter('debut', value: $debut)
            ->orderBy('r.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
