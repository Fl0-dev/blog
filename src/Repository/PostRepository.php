<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByDates(string $from, string $to)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.created_at between :from and :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('create_at','DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function FindBySearch(float|bool|int|string|null $search)
    {
        $qb = $this->createQueryBuilder('p');

            if($search != null)
            {
                $qb ->join('p.user','u')
                    ->addSelect('u')
                    ->Where('u.firstname like :content')
                    ->orWhere('u.lastname like :content')
//                    ->innerJoin('p.categories','c','with',':content member of c.name')
//                    ->addSelect('c')
                    //->innerJoin(Category::class,'c','with','c.name like :content')
                    ->orWhere('p.content like :content')
                    ->orWhere('p.title like :content')
                    ->setParameter('content', '%'.$search.'%')
                    ->orderBy('create_at','DESC')
                    ;
            }

            $query = $qb->getQuery();
            return $query->execute();
    }

    public function FindAllFat()
    {
        return $this->createQueryBuilder('p')
            ->join('p.user','u')
            ->addSelect('u')
            ->join('p.categories', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult()
            ;
    }
}
