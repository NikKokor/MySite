<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiController extends AbstractController
{

    protected int $statusCode = Response::HTTP_OK;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function response($data,array $headers): JsonResponse
    {
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    public function respondWithErrors($errors, $headers = []): JsonResponse
    {
        $data = [
            'status' => $this->getStatusCode(),
            'errors' => $errors,
        ];

        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }


    public function respondWithSuccess($success, $headers = []): JsonResponse
    {
        $data = [
            'status' => $this->getStatusCode(),
            'success' => $success,
        ];

        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }


    public function respondUnauthorized($message = 'Not authorized!'): JsonResponse
    {
        return $this->setStatusCode(Response::HTTP_UNAUTHORIZED)->respondWithErrors($message);
    }

    public function respondValidationError($message = 'Validation errors'): JsonResponse
    {
        return $this->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)->respondWithErrors($message);
    }

    public function respondNotFound($message = 'Not found!'): JsonResponse
    {
        return $this->setStatusCode(Response::HTTP_NOT_FOUND)->respondWithErrors($message);
    }

    public function respondCreated($data = []): JsonResponse
    {
        return $this->setStatusCode(Response::HTTP_CREATED)->response($data);
    }

    // this method allows us to accept JSON payloads in POST requests
    // since Symfony 4 doesn’t handle that automatically:

    protected function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}
