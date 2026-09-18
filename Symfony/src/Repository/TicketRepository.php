<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Service\QueryFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'screeningId' => [QueryFilter::RELATION, 'screening'],
        'customerId' => [QueryFilter::RELATION, 'customer'],
        'seatRow' => QueryFilter::INT,
        'seatNumber' => QueryFilter::INT,
        'price' => [QueryFilter::DECIMAL, 'priceAmount'],
        'status' => QueryFilter::ENUM,
        'purchasedAt' => QueryFilter::DATETIME,
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }
}
