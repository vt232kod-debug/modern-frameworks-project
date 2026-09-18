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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/screenings', name: 'api_screenings_')]
final class ScreeningController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, ScreeningRepository $screenings): JsonResponse
    {
        return $this->listEntities($request, $screenings->createQueryBuilder('s'), ScreeningRepository::FILTERS, 'screening');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Screening $screening): JsonResponse
    {
        return $this->json($screening, context: ['groups' => 'screening:read']);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->save($request, new Screening(), Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Screening $screening): JsonResponse
    {
        return $this->save($request, $screening, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
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
