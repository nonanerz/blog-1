<?php

namespace AppBundle\Repository;
use Doctrine\ORM\EntityRepository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends EntityRepository
{
    public function findLastFive()
    {
        return $this->createQueryBuilder('article')
            ->setMaxResults(5)
            ->orderBy('article.publishedAt', 'DESC')
            ->getQuery()
            ->execute();

    }

}
