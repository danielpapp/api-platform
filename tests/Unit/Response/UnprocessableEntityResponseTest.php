<?php

namespace Tests\Unit\Response;

use App\Response\UnprocessableEntityResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class UnprocessableEntityResponseTest extends TestCase
{
    public function testUnprocessableEntityResponseConstruction(): void
    {
        $violation1 = new ConstraintViolation('Message 1', null, [], null, 'property1', null);
        $violation2 = new ConstraintViolation('Message 2', null, [], null, 'property2', null);
        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $unprocessableEntityResponse = new UnprocessableEntityResponse($violations);

        self::assertInstanceOf(JsonResponse::class, $unprocessableEntityResponse);
        self::assertIsArray($unprocessableEntityResponse->payload);
        self::assertArrayHasKey('errors', $unprocessableEntityResponse->payload);

        $expectedErrors = [
            'property1' => ['Message 1'],
            'property2' => ['Message 2'],
        ];

        self::assertEquals($expectedErrors, $unprocessableEntityResponse->payload['errors']);
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $unprocessableEntityResponse->getStatusCode());
    }
}
