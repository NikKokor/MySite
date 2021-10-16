<?php
namespace App\Repository;

use App\Entity\Logbook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * @method Logbook|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logbook|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logbook[]    findAll()
 * @method Logbook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogbookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logbook::class);
    }
}