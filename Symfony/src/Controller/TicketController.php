<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Screening;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Clients see and buy only their own tickets; managers and admins work with all tickets.
 */
#[Route('/api/tickets', name: 'api_tickets_')]
final class TicketController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, TicketRepository $tickets): JsonResponse
    {
        $qb = $tickets->createQueryBuilder('t');
        if (!$this->isGranted('ROLE_MANAGER')) {
            $qb->andWhere('t.customer = :me')->setParameter('me', $this->currentUser()->getCustomer());
        }

        return $this->listEntities($request, $qb, TicketRepository::FILTERS, 'ticket');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Ticket $ticket): JsonResponse
    {
        if (!$this->isGranted('ROLE_MANAGER') && $ticket->getCustomer() !== $this->currentUser()->getCustomer()) {
            throw $this->createAccessDeniedException('You can view only your own tickets.');
        }

        return $this->json($ticket, context: ['groups' => 'ticket:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $ticket = new Ticket();
        if ($this->isGranted('ROLE_MANAGER')) {
            return $this->save($request, $ticket, Response::HTTP_CREATED);
        }

        // A client buys a ticket for himself: customer, status and price are not taken from the request
        $customer = $this->currentUser()->getCustomer();
        if ($customer === null) {
            throw $this->createAccessDeniedException('Your account has no customer profile.');
        }

        return $this->saveEntity($request, $ticket, 'ticket', Response::HTTP_CREATED, [
            'screeningId' => [Screening::class, $ticket->setScreening(...)],
        ], function () use ($ticket, $customer) {
            $ticket->setCustomer($customer)
                ->setStatus('reserved')
                ->setPrice($ticket->getScreening()?->getPrice());
        });
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        return $this->save($request, $ticket, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
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

    private function currentUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }
}
