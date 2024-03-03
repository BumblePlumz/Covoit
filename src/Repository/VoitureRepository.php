<?php

namespace App\Repository;

use App\Entity\Voiture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Voiture>
 *
 * @method Voiture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Voiture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Voiture[]    findAll()
 * @method Voiture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoitureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voiture::class);
    }
    
    public function findAllWithPersons(): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.personnes', 'p') // Jointure avec la collection de personnes
            ->getQuery()
            ->getResult();
    }

    public function findAllWithMarque(): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.marque', 'm') // Jointure avec la collection de personnes
            ->getQuery()
            ->getResult();
    }

    public function findCarByIdWithMarque(int $id): ?Voiture
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.marque', 'm') // Join with the brand
            ->andWhere('v.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
