<?php

namespace App\Repository;

use App\Entity\Personne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Personne>
 *
 * @method Personne|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personne|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personne[]    findAll()
 * @method Personne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personne::class);
    }

    public function findPassanger(): array
    {
        $em = $this->getEntityManager();
        $personneRepository = $em->getRepository(Personne::class);
        $query = $personneRepository->createQueryBuilder('p')
            ->leftJoin('p.trajets', 't')
            ->getQuery();
        $personnesAvecTrajets = $query->getResult();
        $em->flush();
        return $personnesAvecTrajets;
    }
    public function findPersonneWithDependenciesById(int $personneId): ?Personne
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.utilisateur', 'u')
            ->leftJoin('p.ville', 'v')
            ->leftJoin('p.voiture', 'vo')
            ->leftJoin('p.trajetsConducteur', 'tc')
            ->leftJoin('p.trajetsPassager', 'tp')
            ->leftJoin('tp.conducteur', 't_pc') // Join with conducteur of trajetsPassager
            ->leftJoin('tp.villeDepart', 't_pvd') // Join with villeDepart of trajetsPassager
            ->leftJoin('tp.villeArriver', 't_pva') // Join with villeArrivee of trajetsPassager
            ->where('p.id = :personneId')
            ->setParameter('personneId', $personneId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
