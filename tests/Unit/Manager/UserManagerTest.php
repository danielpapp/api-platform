<?php

namespace Tests\Unit\Manager;

use App\Entity\User;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;
    private UserPasswordHasherInterface|MockObject $passwordHasher;
    private EntityManagerInterface|MockObject $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->userManager = new UserManager($this->passwordHasher, $this->entityManager);
    }

    public function testSaveMethodWithPasswordHashing(): void
    {
        $user = new User();
        $user->setPlainPassword('plain-password');

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'plain-password')
            ->willReturn('hashed-password');

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->userManager->save($user);

        self::assertSame('hashed-password', $user->getPassword());
    }

    public function testSaveMethodWithoutPasswordHashing(): void
    {
        $user = new User();

        $this->passwordHasher
            ->expects($this->never())
            ->method('hashPassword');

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->userManager->save($user);
    }
}
