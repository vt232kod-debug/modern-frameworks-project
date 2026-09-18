<?php

namespace App\Controller;

use App\Entity\Hall;
use App\Entity\Movie;
use App\Entity\Screening;
use App\Repository\ScreeningRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/screenings', name: 'api_screenings_')]
final class ScreeningController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ScreeningRepository $screenings): JsonResponse
    {
        return $this->json($screenings->findAll(), context: ['groups' => 'screening:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Screening $screening): JsonResponse
    {
        return $this->json($screening, context: ['groups' => 'screening:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->save($request, new Screening(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Screening $screening): JsonResponse
    {
        return $this->save($request, $screening, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Screening $screening): JsonResponse
    {
        return $this->deleteEntity($screening);
    }

    private function save(Request $request, Screening $screening, int $status): JsonResponse
    {
        return $this->saveEntity($request, $screening, 'screening', $status, [
            'movieId' => [Movie::class, $screening->setMovie(...)],
            'hallId' => [Hall::class, $screening->setHall(...)],
        ]);
    }
}
