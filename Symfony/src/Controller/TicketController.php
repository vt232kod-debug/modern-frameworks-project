<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Screening;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tickets', name: 'api_tickets_')]
final class TicketController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, TicketRepository $tickets): JsonResponse
    {
        return $this->listEntities($request, $tickets->createQueryBuilder('t'), TicketRepository::FILTERS, 'ticket');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Ticket $ticket): JsonResponse
    {
        return $this->json($ticket, context: ['groups' => 'ticket:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->save($request, new Ticket(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        return $this->save($request, $ticket, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Ticket $ticket): JsonResponse
    {
        return $this->deleteEntity($ticket);
    }

    private function save(Request $request, Ticket $ticket, int $status): JsonResponse
    {
        return $this->saveEntity($request, $ticket, 'ticket', $status, [
            'screeningId' => [Screening::class, $ticket->setScreening(...)],
            'customerId' => [Customer::class, $ticket->setCustomer(...)],
        ], function () use ($ticket) {
            // Ticket price defaults to the screening price
            if ($ticket->getPrice() === null && $ticket->getScreening() !== null) {
                $ticket->setPrice($ticket->getScreening()->getPrice());
            }
        });
    }
}
