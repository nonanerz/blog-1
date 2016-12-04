<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

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
