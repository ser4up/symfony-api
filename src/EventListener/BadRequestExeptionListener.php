<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * It prepares response for bad request exception.
 */
#[AsEventListener(priority: 1)]
class BadRequestExeptionListener
{
    public function __invoke(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof BadRequestHttpException) {
            return;
        }

        $response = new JsonResponse(
            [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'mesage' => $exception->getMessage(),
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['Content-Type' => 'application/json']
        );

        $event->setResponse($response);
    }
}
