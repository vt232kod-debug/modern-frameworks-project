<?php

namespace App\Repository;

use App\Entity\Screening;
use App\Service\QueryFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Screening>
 */
class ScreeningRepository extends ServiceEntityRepository
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'movieId' => [QueryFilter::RELATION, 'movie'],
        'hallId' => [QueryFilter::RELATION, 'hall'],
        'startsAt' => QueryFilter::DATETIME,
        'price' => [QueryFilter::DECIMAL, 'priceAmount'],
        'language' => QueryFilter::STRING,
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Screening::class);
    }
}
