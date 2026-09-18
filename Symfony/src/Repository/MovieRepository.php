<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Service\QueryFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 */
class MovieRepository extends ServiceEntityRepository
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'title' => QueryFilter::STRING,
        'description' => QueryFilter::STRING,
        'genre' => QueryFilter::STRING,
        'durationMinutes' => QueryFilter::INT,
        'releaseDate' => QueryFilter::DATE,
        'ageRating' => QueryFilter::ENUM,
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }
}
