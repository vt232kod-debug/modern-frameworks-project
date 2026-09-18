<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/customers', name: 'api_customers_')]
final class CustomerController extends ApiController
{
    #[IsGranted('ROLE_MANAGER')]
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, CustomerRepository $customers): JsonResponse
    {
        return $this->listEntities($request, $customers->createQueryBuilder('c'), CustomerRepository::FILTERS, 'customer');
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Customer $customer): JsonResponse
    {
        return $this->json($customer, context: ['groups' => 'customer:read']);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->saveEntity($request, new Customer(), 'customer', Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Customer $customer): JsonResponse
    {
        return $this->saveEntity($request, $customer, 'customer', Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Customer $customer): JsonResponse
    {
        return $this->deleteEntity($customer);
    }
}
