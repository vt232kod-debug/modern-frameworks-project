<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/movies', name: 'api_movies_')]
final class MovieController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, MovieRepository $movies): JsonResponse
    {
        return $this->listEntities($request, $movies->createQueryBuilder('m'), MovieRepository::FILTERS, 'movie');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Movie $movie): JsonResponse
    {
        return $this->json($movie, context: ['groups' => 'movie:read']);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->saveEntity($request, new Movie(), 'movie', Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Movie $movie): JsonResponse
    {
        return $this->saveEntity($request, $movie, 'movie', Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Movie $movie): JsonResponse
    {
        return $this->deleteEntity($movie);
    }
}
