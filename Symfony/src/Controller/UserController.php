<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserPasswordService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * User management (roles are assigned here) — admins only.
 */
#[Route('/api/users', name: 'api_users_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends ApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, UserRepository $users): JsonResponse
    {
        return $this->listEntities($request, $users->createQueryBuilder('u'), UserRepository::FILTERS, 'user');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, context: ['groups' => 'user:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserPasswordService $passwords): JsonResponse
    {
        return $this->save($request, new User(), Response::HTTP_CREATED, $passwords);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, User $user, UserPasswordService $passwords): JsonResponse
    {
        return $this->save($request, $user, Response::HTTP_OK, $passwords);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(User $user): JsonResponse
    {
        return $this->deleteEntity($user);
    }

    private function save(Request $request, User $user, int $status, UserPasswordService $passwords): JsonResponse
    {
        return $this->saveEntity($request, $user, 'user', $status, [
            'customerId' => [Customer::class, $user->setCustomer(...)],
        ], fn () => $passwords->hashPlainPassword($user));
    }
}
