<?php

namespace App\Repository;

use App\Entity\User;
use App\Service\QueryFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'email' => QueryFilter::STRING,
        'role' => QueryFilter::ENUM,
        'customerId' => [QueryFilter::RELATION, 'customer'],
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
