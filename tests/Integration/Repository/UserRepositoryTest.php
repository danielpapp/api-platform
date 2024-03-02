<?php

namespace Tests\Integration\Repository;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserRepositoryTest extends WebTestCase
{
    use Factories, ResetDatabase;

    private EntityManagerInterface|ObjectManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = self::bootKernel()->getContainer()->get('doctrine')->getManager();
    }

    public function testFindUsersAfterCursor(): void
    {
        UserFactory::createMany(10);

        $cursor = 5;
        $limit = 5;

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findUsersAfterCursor($cursor, $limit);

        self::assertCount($limit, $users);

        foreach ($users as $user) {
            self::assertGreaterThan($cursor, $user->getId());
        }
    }
}
