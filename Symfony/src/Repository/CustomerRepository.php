<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Service\QueryFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'firstName' => QueryFilter::STRING,
        'lastName' => QueryFilter::STRING,
        'email' => QueryFilter::STRING,
        'phone' => QueryFilter::STRING,
        'birthDate' => QueryFilter::DATE,
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }
}
