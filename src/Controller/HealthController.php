<?php

namespace App\Controller;

use App\Service\HealthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends ApiController
{
    /**
     * @Route("/health", name="health", methods={"GET"})
     */
    public function index(HealthService $healthService): JsonResponse
    {
        return $this->response([
            'status' => Response::HTTP_OK,
            'APP_ENV' => $healthService->getAppEnv(),
        ], []);
    }
}
