<?php

namespace Tests\Unit\Response;

use App\Response\PaginationResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaginationResponseTest extends TestCase
{
    /**
     * @param array<string|object> $data
     */
    #[DataProvider('itemProvider')]
    public function testPaginationResponseConstruction(array $data, string $lastItemId): void
    {
        $status = 200;
        $headers = ['Content-Type' => 'application/json'];

        $paginationResponse = new PaginationResponse($data, $status, $headers);

        self::assertInstanceOf(JsonResponse::class, $paginationResponse);
        self::assertIsArray($paginationResponse->payload);
        self::assertArrayHasKey('items', $paginationResponse->payload);
        self::assertArrayHasKey('nextCursor', $paginationResponse->payload);
        self::assertEquals(base64_encode($lastItemId), $paginationResponse->payload['nextCursor']);
        self::assertEquals($status, $paginationResponse->getStatusCode());
        self::assertEquals($headers['Content-Type'], $paginationResponse->headers->get('Content-Type'));
    }

    /**
     * @return iterable<string, array{array<string|object>, string}>
     */
    public static function itemProvider(): iterable
    {
        yield 'scalar items' => [
            ['item1', 'item2', 'item3'],
            'item3',
        ];
        yield 'objects with getId method' => [
            [self::createObject(1), self::createObject(2), self::createObject(3)],
            '3',
        ];
    }

    private static function createObject(int $id): object
    {
        return new class($id) {
            private int $id;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getId(): int
            {
                return $this->id;
            }
        };
    }
}
