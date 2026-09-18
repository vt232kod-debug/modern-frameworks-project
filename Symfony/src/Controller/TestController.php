<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'framework' => 'Symfony ' . \Symfony\Component\HttpKernel\Kernel::VERSION,
            'message' => 'Test method works',
        ]);
    }
}
