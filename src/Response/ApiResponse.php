<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    public readonly mixed $payload;

    /**
     * @param array<mixed> $headers
     */
    public function __construct(mixed $data = null, int $status = 200, array $headers = [])
    {
        $this->payload = $data;

        parent::__construct($data, $status, $headers);
    }
}
