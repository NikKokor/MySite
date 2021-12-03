<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Class ApiLoginController
 * @package App\Controller
 * @Route("/")
 */
class ApiLoginController extends AbstractController
{
    /**
     * @Route("/check_login", name="api_login", methods={"POST"})
     */
    public function checkLogin(#[CurrentUser] ?User $user): Response
    {

        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        };

        $token = $user->getPassword();
        return $this->json([
            'token' => $token,
        ]);
    }
}