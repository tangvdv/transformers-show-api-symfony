<?php

namespace App\Repository;

use App\Entity\Artefact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artefact>
 */
class ArtefactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artefact::class);
    }

    public function findAllWithParams($limit, $show = null)
    {
        $query = $this->createQueryBuilder('a');
            //SHOW
            if($show !== null){
                $query->leftJoin('a.show', 's')
                    ->addSelect('s')
                    ->where('s.show_name LIKE :name')
                    ->setParameter('name', '%'.$show.'%');
            }

            return $query->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    public function findOneWithParams(array $params)
    {
        $query = $this->createQueryBuilder('a')
                    //ENTITY
                    ->leftJoin('a.entity', 'e')
                    ->addSelect('e');

                    foreach($params as $key => $value){
                        if($key === "id"){
                            $query->where('a.id = :id')
                                ->setParameter('id', $value);
                        }
                        else if($key === "name"){
                            $query->where('e.entity_name LIKE :entity_name')
                                ->setParameter('entity_name', '%'.$value.'%');
                        }

                        if($key === "show"){
                            $query->leftJoin('a.show', 's')
                                ->addSelect('s')
                                ->andWhere('s.show_name LIKE :show_name')
                                ->setParameter('show_name', '%'.$value.'%');
                        }
                    }

                    return $query->getQuery()
                                ->getOneOrNullResult();
    }
}
