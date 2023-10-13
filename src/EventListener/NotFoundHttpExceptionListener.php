<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * It prepares response for bad request exception.
 */
#[AsEventListener(priority: 1)]
class NotFoundHttpExceptionListener
{
    public function __invoke(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        $response = new JsonResponse(
            [
                'status' => Response::HTTP_NOT_FOUND,
                'mesage' => $exception->getMessage(),
            ],
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/json']
        );

        $event->setResponse($response);
    }
}
