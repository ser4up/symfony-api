<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * It prepares response for validation exception.
 */
#[AsEventListener(priority: 2)]
class ValidationExeptionListener
{
    public function __invoke(ExceptionEvent $event)
    {
        $exception = $event?->getThrowable()?->getPrevious();
        if (!$exception instanceof ValidationFailedException) {
            return;
        }

        $violations = $exception->getViolations();

        $errors = [];
        /* @var $violation ConstraintViolation */
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        $response = new JsonResponse(
            [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'mesage' => 'Validation error.',
                'errors' => $errors,
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['Content-Type' => 'application/json']
        );

        $event->setResponse($response);
    }
}
