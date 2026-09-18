<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Service\UserPasswordService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth', name: 'api_auth_')]
final class AuthController extends ApiController
{
    /**
     * Handled by the "login" firewall (json_login + LexikJWT) in security.yaml:
     * {"email": "...", "password": "..."} → {"token": "<JWT>"}.
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): never
    {
        throw new BadRequestHttpException('Send JSON {"email": "...", "password": "..."} with "Content-Type: application/json".');
    }

    /**
     * Self-registration of a client: creates the user and its customer profile, returns a token.
     */
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserPasswordService $passwords, JWTTokenManagerInterface $jwt): JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\Symfony\Component\HttpFoundation\Exception\JsonException) {
            throw new BadRequestHttpException('Invalid JSON.');
        }
        $text = fn (string $key) => isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : null;

        $customer = (new Customer())
            ->setFirstName($text('firstName'))
            ->setLastName($text('lastName'))
            ->setEmail($text('email'))
            ->setPhone($text('phone'));
        $user = (new User())
            ->setEmail($text('email'))
            ->setRole(User::ROLE_CLIENT)
            ->setPlainPassword($text('password'))
            ->setCustomer($customer);

        $errors = [];
        foreach ([$user, $customer] as $entity) {
            foreach ($this->validator->validate($entity) as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
        }
        if ($errors) {
            return $this->json(['errors' => array_map('array_unique', $errors)], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $passwords->hashPlainPassword($user);
        $this->em->persist($customer);
        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            'user' => $this->normalize($user),
            'token' => $jwt->create($user),
        ], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($user, context: ['groups' => 'user:read']);
    }

    private function normalize(User $user): array
    {
        return $this->serializer->normalize($user, null, ['groups' => 'user:read']);
    }
}
