<?php

namespace App\Repository;

use App\Entity\Hall;
use App\Service\QueryFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hall>
 */
class HallRepository extends ServiceEntityRepository
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'name' => QueryFilter::STRING,
        'type' => QueryFilter::ENUM,
        'rowsCount' => QueryFilter::INT,
        'seatsPerRow' => QueryFilter::INT,
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hall::class);
    }
}
