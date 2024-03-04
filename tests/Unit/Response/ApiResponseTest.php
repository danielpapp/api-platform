<?php

namespace Tests\Unit\Response;

use App\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponseTest extends TestCase
{
    public function testApiResponseConstruction(): void
    {
        $data = ['key' => 'value'];
        $status = 200;
        $headers = ['Content-Type' => 'application/json'];

        $apiResponse = new ApiResponse($data, $status, $headers);

        self::assertInstanceOf(JsonResponse::class, $apiResponse);
        self::assertEquals($data, $apiResponse->payload);
        self::assertEquals($status, $apiResponse->getStatusCode());
        self::assertEquals($headers['Content-Type'], $apiResponse->headers->get('Content-Type'));
    }
}
