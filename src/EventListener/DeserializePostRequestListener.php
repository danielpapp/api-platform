<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEventListener(event: KernelEvents::REQUEST)]
readonly class DeserializePostRequestListener
{
    public function __construct(
        private RouterInterface $router,
        private SerializerInterface $serializer,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        [$resourceType, $deserializationGroups] = $this->getRouteOptions($request);

        try {
            $data = $this->serializer->deserialize(
                $request->getContent(),
                $resourceType,
                $request->getFormat($request->headers->get('Content-Type')) ?? 'json',
                ['groups' => $deserializationGroups]
            );

            $request->attributes->set('data', $data);
        } catch (\Exception) {
            throw new BadRequestHttpException('Invalid request data.');
        }
    }

    /**
     * @return array{string|null, array<string>|null}
     */
    private function getRouteOptions(Request $request): array
    {
        $route = $this->router->getRouteCollection()->get($request->get('_route'));

        return [$route?->getDefault('resource_type'), $route?->getDefault('deserialization_groups') ?? []];
    }
}
