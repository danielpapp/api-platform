<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\Response;

class PaginationResponse extends ApiResponse
{
    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function __construct(array $data = [], int $status = Response::HTTP_OK, array $headers = [])
    {
        parent::__construct($this->getPaginationData($data), $status, $headers);
    }

    /**
     * @param array<mixed> $data
     * @return array{
     *     items: array<mixed>,
     *     nextCursor: string|null,
     * }
     */
    private function getPaginationData(array $data): array
    {
        $lastItem = end($data);

        return [
            'items' => $data,
            'nextCursor' => $lastItem === false ? null : $this->encodeNextCursor($lastItem),
        ];
    }

    private function encodeNextCursor(mixed $lastItem): string
    {
        return base64_encode(
            is_object($lastItem) && method_exists($lastItem, 'getId')
                ? $lastItem->getId()
                : $lastItem
        );
    }
}
