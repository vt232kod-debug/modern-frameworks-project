<?php

namespace App\Controller;

use App\Entity\Hall;
use App\Repository\HallRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/halls', name: 'api_halls_')]
final class HallController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, HallRepository $halls): JsonResponse
    {
        return $this->listEntities($request, $halls->createQueryBuilder('h'), HallRepository::FILTERS, 'hall');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Hall $hall): JsonResponse
    {
        return $this->json($hall, context: ['groups' => 'hall:read']);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->saveEntity($request, new Hall(), 'hall', Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Hall $hall): JsonResponse
    {
        return $this->saveEntity($request, $hall, 'hall', Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Hall $hall): JsonResponse
    {
        return $this->deleteEntity($hall);
    }
}
