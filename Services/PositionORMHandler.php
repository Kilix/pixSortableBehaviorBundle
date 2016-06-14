<?php
/*
 * This file is part of the pixSortableBehaviorBundle.
 *
 * (c) Nicolas Ricci <nicolas.ricci@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pix\SortableBehaviorBundle\Services;

use Doctrine\ORM\EntityManagerInterface;

class PositionORMHandler extends PositionHandler
{

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getLastPosition($entity)
    {

        $query = $this->em->createQuery(sprintf(
            'SELECT MAX(m.%s) FROM %s m',
            $positionFiles = $this->getPositionFieldByEntity($entity),
            $entity
        ));
        $result = $query->getResult();

        if (array_key_exists(0, $result)) {
            return intval($result[0][1]);
        }
        
        return 0;
    }

    public function moveToFirstPosition($idEntity, $entity, $position)
    {
        $qb = $this->em->createQueryBuilder('e')
            ->select("e.id, e.{$this->getPositionFieldByEntity($entity)}")
            ->from($entity, 'e')
            ->where('e.id <> :id_entite')
            ->orderBy("e.{$this->getPositionFieldByEntity($entity)}")
            ->setParameter('id_entite', $idEntity)
        ;
        $results = $qb->getQuery()->execute();

        if($position < 1){
            $position = 1;
        }

        $sortedArticles = [];
        $sortedArticles[$idEntity] = $position;
        $cpt = 1;

        foreach($results as $article) {
            if($cpt == $position) {
                $sortedArticles[$article['id']] = $cpt+1;
                $cpt++;
            }
            else{
                $sortedArticles[$article['id']] = $cpt;
            }

            $cpt++;
        }

        foreach ($sortedArticles as $id=>$position) {
            $qb = $this->em->createQueryBuilder('e')
                ->update($entity, 'e')
                ->set("e.{$this->getPositionFieldByEntity($entity)}", $position)
                ->where('e.id = ' . $id)
                ->getQuery()->execute()
            ;
        }
    }


}
