<?php

namespace Tests\Application\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\Application\ApiTestCase;
use Zenstruck\Foundry\Proxy;

class UserControllerTest extends ApiTestCase
{
    #[DataProvider('supportedFormats')]
    public function testListAction(string $format): void
    {
        $users = UserFactory::createMany(10);

        self::get($format, '/api/users');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', $format);

        self::assertPaginationItemsSame($format, array_map(static fn (User|Proxy $user): array => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d\TH:i:sP'),
        ], $users));
    }

    #[DataProvider('supportedFormats')]
    public function testShowAction(string $format): void
    {
        $user = UserFactory::createOne();

        self::get($format, '/api/users/'.$user->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', $format);

        self::assertResponseDataSame($format, [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d\TH:i:sP'),
        ]);
    }

    #[DataProvider('supportedFormats')]
    public function testCreateAction(string $format): void
    {
        self::post($format, '/api/users', [
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'plainPassword' => 'secret',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('Content-Type', $format);

        $data = self::getResponseData($format);
        self::assertSame(1, $data['id']);
        self::assertSame('john@example.com', $data['email']);
        self::assertSame('John', $data['firstName']);
        self::assertSame('Doe', $data['lastName']);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayNotHasKey('plainPassword', $data);
    }

    #[DataProvider('supportedFormats')]
    public function testInvalidCreateAction(string $format): void
    {
        $user = UserFactory::createOne();

        self::post($format, '/api/users', [
            'email' => $user->getEmail(),
        ]);

        self::assertResponseIsUnprocessable();
        self::assertResponseHeaderSame('Content-Type', $format);
        self::assertResponseDataSame($format, [
            'errors' => [
                'email' => ['This value is already used.'],
                'firstName' => ['This value should not be blank.'],
                'lastName' => ['This value should not be blank.'],
                'plainPassword' => ['This value should not be blank.'],
            ],
        ]);
    }
}
