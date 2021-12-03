<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    /**
     * @Route("/login", name="api_login")
     */
    public function index(): Response
    {
	return $this->json([
            'token' => $this->getUser() ? $this->getUser()->getPassword() : null
        ]);
    }
}
