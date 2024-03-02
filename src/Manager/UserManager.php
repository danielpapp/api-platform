<?php

namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user): void
    {
        if ($user->getPlainPassword() !== '') {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
