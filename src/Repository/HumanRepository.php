<?php

namespace App\Repository;

use App\Entity\Human;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Human>
 */
class HumanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Human::class);
    }

    public function findAllWithParams($limit, $show = null)
    {
        $query = $this->createQueryBuilder('h');
            //SHOW
            if($show !== null){
                $query->leftJoin('h.show', 's')
                    ->addSelect('s')
                    ->where('s.show_name LIKE :show_name')
                    ->setParameter('show_name', '%'.$show.'%');
            }

            return $query->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    public function findOneWithParams(array $params)
    {
        $query = $this->createQueryBuilder('h')
                    //ENTITY
                    ->leftJoin('h.entity', 'e')
                    ->addSelect('e')

                    //SHOW
                    ->leftJoin('h.show', 's')
                    ->addSelect('s');
 
                    foreach($params as $key => $value){
                        if($key === "id"){
                            $query->where('h.id = :id')
                                ->setParameter('id', $value);
                        }
                        else if($key === "name"){
                            $query->where('e.entity_name LIKE :name')
                                ->setParameter('name', '%'.$value.'%');
                        }

                        if($key === "show" && $value !== null){
                            $query->andWhere('s.show_name LIKE :show')
                                ->setParameter('show', '%'.$value.'%');
                        }
                    }

                    return $query->getQuery()
                            ->getOneOrNullResult();
    }
}
