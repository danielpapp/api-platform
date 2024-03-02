<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST)]
class PaginationRequestListener
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->query->has('cursor')) {
            $request->attributes->set('cursor', base64_decode($request->query->get('cursor'), true));
        }

        if ($request->query->has('limit')) {
            $request->attributes->set('limit', $request->query->getInt('limit'));
        }
    }
}
