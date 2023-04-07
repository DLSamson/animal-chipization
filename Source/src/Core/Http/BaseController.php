<?php

namespace Api\Core\Http;

use Api\Core\Factories\ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseController implements ControllerInterface
{

    protected LoggerInterface $log;
    protected ValidatorInterface $validator;

    public function __construct(LoggerInterface $log, ValidatorInterface $validator)
    {
        $this->log = $log;
        $this->validator = $validator;
    }

    public function handle(Request $request, Response $response, array $args = []): Response
    {
        $errors = $this->validateRequest($request, $args);
        if ($errors) return ResponseFactory::MakeJSON($errors)->BadRequest();
        return $this->process($request, $response, $args);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        return [];
    }

    protected function validate($data, $constraints)
    {
        $violations = $this->validator->validate($data, $constraints);

        if ($violations->count() === 0) return [];

        /* If has errors */
        $errors = [];
        foreach ($violations as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return $errors;
    }

    protected function process(Request $request, Response $response, array $args = []): Response
    {
        return ResponseFactory::Make()->Success();
    }
}