<?php

namespace App\Repository;

use App\Entity\Duyuru;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Duyuru|null find($id, $lockMode = null, $lockVersion = null)
 * @method Duyuru|null findOneBy(array $criteria, array $orderBy = null)
 * @method Duyuru[]    findAll()
 * @method Duyuru[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DuyuruRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Duyuru::class);
    }

    // /**
    //  * @return Duyuru[] Returns an array of Duyuru objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Duyuru
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    //******left join with sql********
    public function getAllDuyurular(): array
    {
        $conn=$this->getEntityManager()->getConnection();
        $sql='
            SELECT d.*,c.title as catname,u.name,u.surname FROM duyuru d
            JOIN category c ON c.id=d.category_id
            JOIN user u ON u.id=d.userid
            ORDER BY c.title ASC
        ';
        $stmt=$conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
