<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UnprocessableEntityResponse extends ApiResponse
{
    public function __construct(ConstraintViolationListInterface $violations)
    {
        parent::__construct(
            ['errors' => $this->getErrorsFromViolationList($violations)],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * @return array<string, non-empty-array<int,string>>
     */
    private function getErrorsFromViolationList(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errors;
    }
}
