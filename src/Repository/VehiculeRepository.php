<?php

namespace App\Repository;

use App\Entity\Vehicule;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    private $em;
    private $conn;

    public function __construct(ManagerRegistry $registry)
    {
        $this->em = $registry->getManager();
        $this->conn = $registry->getConnection();
        parent::__construct($registry, Vehicule::class);
    }

    public function getVehiculeEquiv($reservation_id)
    {
        $resa = $this->em
            ->getRepository(Reservation::class)
            ->findOneBy(['id' => $reservation_id]);

        $vl = $resa->getVehicule();
        $immat = $vl->getImmatriculation();
        $nb_places = $vl->getNbPlaces();
        $serigraphie = intval($vl->isSerigraphie());
        $debut = $resa->getDateDebut()->format('Y-m-d') . ' ' . $resa->getHeureDebut();
        $fin = $resa->getDateFin()->format('Y-m-d') . ' ' . $resa->getHeureFin();

        $sql = <<<SQL
            SELECT 
                DISTINCT v.id,
                v.marque,
                v.modele,
                v.nb_places,
                v.immatriculation,
                t.code as transmission,
                c.code as carburant
            FROM
                vehicule v
            LEFT JOIN reservation r ON v.id = r.vehicule_id
            LEFT JOIN statut_reservation s ON r.statut_id = s.id
            JOIN transmission_vehicule t ON v.transmission_id = t.Id
            JOIN carburant_vehicule c ON v.carburant_id = c.id
            WHERE(
                v.immatriculation != "$immat"
            AND
                v.nb_places >= $nb_places
            AND
                v.serigraphie = $serigraphie
            )
            AND (
                (r.id IS NULL OR s.code = 'Annulée')
                OR (
                    (
                        "$debut" NOT BETWEEN CONCAT(CONCAT(r.date_debut, ' '), r.heure_debut) AND CONCAT(CONCAT(r.date_fin, ' '), r.heure_fin)
                    AND
                        "$fin" NOT BETWEEN CONCAT(CONCAT(r.date_debut, ' '), r.heure_debut) AND CONCAT(CONCAT(r.date_fin, ' '), r.heure_fin)
                    )
                    OR s.code != 'Annulée'
                )
            )
            GROUP BY v.id
            ;
SQL;

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->executeQuery();
        return $result->fetchAllAssociative();
    }

    //    /**
    //     * @return Vehicule[] Returns an array of Vehicule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicule
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
