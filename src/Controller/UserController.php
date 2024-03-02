<?php

namespace App\Controller;

use App\Entity\User;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use App\Response\ApiResponse;
use App\Response\PaginationResponse;
use App\Response\UnprocessableEntityResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(
    '/api/users',
    name: 'api.users.',
    defaults: [
        'resource_type' => User::class,
        'serialization_groups' => ['user:read'],
        'deserialization_groups' => ['user:write'],
    ],
)]
#[AsController]
class UserController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(Request $request, UserRepository $repository): PaginationResponse
    {
        return new PaginationResponse($repository->findUsersAfterCursor(
            $request->attributes->getInt('cursor'),
            $request->attributes->getInt('limit', 30),
        ));
    }

    #[Route('/{id<\d+>}', name: 'show', methods: [Request::METHOD_GET])]
    public function show(User $user): ApiResponse
    {
        return new ApiResponse($user);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request, ValidatorInterface $validator, UserManager $manager): ApiResponse
    {
        $violations = $validator->validate(
            $user = $request->attributes->get('data'),
            groups: ['Default', 'setPassword']
        );

        if ($violations->count() === 0) {
            $manager->save($user);

            return new ApiResponse($user, Response::HTTP_CREATED);
        }

        return new UnprocessableEntityResponse($violations);
    }
}
