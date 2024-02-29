<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserJsonResourceTest extends ApiTestCase
{
    use Factories, ResetDatabase;

    public function testGetCollection(): void
    {
        UserFactory::createMany(100);

        $response = static::createClient()->request('GET', '/api/users');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/users?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?page=1',
                'hydra:last' => '/api/users?page=4',
                'hydra:next' => '/api/users?page=2',
            ],
        ]);
        self::assertCount(30, $response->toArray()['hydra:member']);
        self::assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testGetUser(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne();

        static::createClient()->request('GET', '/api/users/'.$user->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d\TH:i:sP'),
        ]);
        self::assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testCreateUser(): void
    {
        $response = static::createClient()->request('POST', '/api/users', ['json' => [
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'password' => 'secret',
        ]]);

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'id' => 1,
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);
        self::assertMatchesRegularExpression('~^/api/users/\d+$~', $response->toArray()['@id']);
        self::assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testCreateInvalidUser(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne();

        static::createClient()->request('POST', '/api/users', ['json' => [
            'email' => $user->getEmail(),
        ]]);

        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
        self::assertJsonContains([
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'email: This value is already used.'.PHP_EOL
                .'firstName: This value should not be blank.'.PHP_EOL
                .'lastName: This value should not be blank.'.PHP_EOL
                .'password: This value should not be blank.'
        ]);
    }
}
