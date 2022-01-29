<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends ApiController
{
    /**
     * @Route("/test", name="test")
     */
    public function index(): JsonResponse
    {
        return $this->response([
            'message' => 'update test!'
        ], []);
    }
}
