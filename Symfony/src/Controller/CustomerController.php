<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/customers', name: 'api_customers_')]
final class CustomerController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CustomerRepository $customers): JsonResponse
    {
        return $this->json($customers->findAll(), context: ['groups' => 'customer:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Customer $customer): JsonResponse
    {
        return $this->json($customer, context: ['groups' => 'customer:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->saveEntity($request, new Customer(), 'customer', Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Customer $customer): JsonResponse
    {
        return $this->saveEntity($request, $customer, 'customer', Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Customer $customer): JsonResponse
    {
        return $this->deleteEntity($customer);
    }
}
