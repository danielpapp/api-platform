<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Factory\UserFactory;
use Symfony\Component\Yaml\Yaml;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserYamlResourceTest extends ApiTestCase
{
    use Factories, ResetDatabase;

    public function testGetCollection(): void
    {
        UserFactory::createMany(100);

        // 100 users with 30 items per page will result in 4 pages
        $lastPage = 4;
        $currentPage = 1;

        while ($currentPage <= $lastPage) {
            $response = static::createClient()->request('GET', '/api/users', [
                'headers' => ['Accept' => 'application/x-yaml'],
                'query' => ['page' => $currentPage],
            ]);

            self::assertResponseIsSuccessful();
            self::assertResponseHeaderSame('content-type', 'application/x-yaml; charset=utf-8');

            $parsedYaml = Yaml::parse($response->getContent());
            self::assertCount($currentPage === $lastPage ? 10 : 30, $parsedYaml);

            $sampleElement = $parsedYaml[0];
            self::assertArrayHasKey('id', $sampleElement);
            self::assertArrayHasKey('email', $sampleElement);
            self::assertArrayHasKey('firstName', $sampleElement);
            self::assertArrayHasKey('lastName', $sampleElement);
            self::assertArrayHasKey('createdAt', $sampleElement);
            self::assertArrayNotHasKey('password', $sampleElement);

            $currentPage++;
        }
    }

    public function testGetUser(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne();

        $response = static::createClient()->request('GET', '/api/users/'.$user->getId(), [
            'headers' => ['Accept' => 'application/x-yaml'],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/x-yaml; charset=utf-8');

        self::assertSame([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d\TH:i:sP'),
        ], Yaml::parse($response->getContent()));
    }

    public function testCreateUser(): void
    {
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/x-yaml'],
            'json' => [
                'email' => 'john@example.com',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'password' => 'secret',
            ],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/x-yaml; charset=utf-8');

        $parsedYaml = Yaml::parse($response->getContent());

        self::assertEquals(1, $parsedYaml['id']);
        self::assertEquals('john@example.com', $parsedYaml['email']);
        self::assertEquals('John', $parsedYaml['firstName']);
        self::assertEquals('Doe', $parsedYaml['lastName']);
        self::assertArrayNotHasKey('password', $parsedYaml);
    }

    public function testCreateInvalidUser(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne();

        static::createClient()->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/x-yaml'],
            'json' => ['email' => $user->getEmail()],
        ]);

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
