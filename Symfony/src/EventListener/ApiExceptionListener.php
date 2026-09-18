<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Returns errors of /api/* routes as JSON instead of HTML error pages.
 */
#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ApiExceptionListener
{
    public function __construct(private readonly bool $debug = false)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $message = $status === 404 ? 'Resource not found.' : $exception->getMessage();
            $headers = $exception->getHeaders();
        } else {
            $status = 500;
            $message = $this->debug ? $exception->getMessage() : 'Internal server error.';
            $headers = [];
        }

        $event->setResponse(new JsonResponse(['error' => $message], $status, $headers));
    }
}
