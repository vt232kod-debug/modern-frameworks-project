<?php

namespace App\Controller;

use App\Entity\Hall;
use App\Repository\HallRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/halls', name: 'api_halls_')]
final class HallController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(HallRepository $halls): JsonResponse
    {
        return $this->json($halls->findAll(), context: ['groups' => 'hall:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Hall $hall): JsonResponse
    {
        return $this->json($hall, context: ['groups' => 'hall:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->saveEntity($request, new Hall(), 'hall', Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Hall $hall): JsonResponse
    {
        return $this->saveEntity($request, $hall, 'hall', Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Hall $hall): JsonResponse
    {
        return $this->deleteEntity($hall);
    }
}
