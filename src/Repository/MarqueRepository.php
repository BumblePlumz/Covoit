<?php

namespace App\Repository;

use App\Entity\Marque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Marque>
 *
 * @method Marque|null find($id, $lockMode = null, $lockVersion = null)
 * @method Marque|null findOneBy(array $criteria, array $orderBy = null)
 * @method Marque[]    findAll()
 * @method Marque[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Marque::class);
    }

    public function findMarqueWithVoitures($marqueId)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.voitures', 'v')
            ->where('m.id = :marqueId')
            ->setParameter('marqueId', $marqueId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findMarqueWithVoituresAndDriver($marqueId)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.voitures', 'v')
            ->leftJoin('v.personnes', 'p') // Jointure avec la collection de personnes
            ->where('m.id = :marqueId')
            ->setParameter('marqueId', $marqueId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
