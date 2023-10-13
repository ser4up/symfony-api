<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * It correct JSON format for not success responses.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: 1)]
class ResponseListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event?->getRequest()?->headers?->contains('content-type', 'application/json')
            || 300 > $event->getResponse()->getStatusCode()) {
            return;
        }

        $responseArr = json_decode($event?->getResponse()?->getContent(), true);

        if (isset($responseArr['code']) && isset($responseArr['message'])) {
            $status = $responseArr['code'];
            $message = $responseArr['message'];
        } elseif (isset($responseArr['status']) && isset($responseArr['detail'])) {
            $status = $responseArr['status'];
            $message = $responseArr['detail'];
        } else {
            return;
        }

        $responseFinal = new JsonResponse(
            [
                'status' => $status,
                'message' => $message,
            ],
            Response::HTTP_UNAUTHORIZED,
            ['Content-Type' => 'application/json']
        );

        $event->setResponse($responseFinal);
    }
}
