<?php

namespace App\EventListener;

use App\Response\ApiResponse;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEventListener(event: KernelEvents::RESPONSE)]
class SerializeApiResponseListener
{
    /** @var array<string, string> */
    private static array $replaceFormats = ['html' => 'json'];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$response instanceof ApiResponse) {
            return;
        }

        $format = $request->getPreferredFormat();
        $format = self::$replaceFormats[$format] ?? $format;

        $content = $this->serializer->serialize($response->payload, $format, [
            'groups' => $this->getSerializationGroups($request),
        ]);

        $event->setResponse(new Response($content, $response->getStatusCode(), [
            'Content-Type' => $request->getMimeType($format),
        ]));
    }

    /**
     * @return array<string>
     */
    private function getSerializationGroups(Request $request): array
    {
        $route = $this->router->getRouteCollection()->get($request->get('_route'));

        return $route?->getDefault('serialization_groups') ?? [];
    }
}
