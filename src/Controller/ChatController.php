<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Driver\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ChatController
 * @package App\Controller
 * @Route("/chat")
 */
class ChatController extends ApiController
{
    /**
     * @Route("/add", name="chat_add", methods={"POST"})
     */
    public function addChat(Request $request, UserRepository $userRepository): JsonResponse
    {
        try {
            $token = $request->headers->get('Token');
            $user = $userRepository->findOneBy(["password" => $token]);

            if ($user == null)
                return $this->responsStatus(Response::HTTP_UNAUTHORIZED, "Token invalid", [Response::HTTP_UNAUTHORIZED]);

            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $chat = new chat();
            $chat->setUser1($user->getId());
            $chat->setUser2($request->get('user_id'));
            $entityManager->persist($chat);
            $entityManager->flush();

            return $this->responsStatus(Response::HTTP_OK, "Chat create successfully");
        } catch (\Exception $e) {
            return $this->responsStatus(Response::HTTP_UNPROCESSABLE_ENTITY, "Data no valid", [Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/get", name="chat_get", methods={"POST"})
     */
    public function getChat(Request $request, UserRepository $userRepository, ChatRepository $chatRepository): JsonResponse
    {
        $token = $request->headers->get('Token');
        $user = $userRepository->findOneBy(["password" => $token]);

        if ($user == null)
            return $this->responsStatus(Response::HTTP_UNAUTHORIZED, "Token invalid", [Response::HTTP_UNAUTHORIZED]);

        $request = $this->transformJsonBody($request);
        $chat = $chatRepository->findOneBy(["user_1" => $user->getId(), "user_2" => $request->get('user_id')]);
        $ch = $chatRepository->findOneBy(["user_2" => $user->getId(), "user_1" => $request->get('user_id')]);

        if (!$chat && !$ch)
            return $this->responsStatus(Response::HTTP_NOT_FOUND, "Chat not found", [Response::HTTP_NOT_FOUND]);

        if ($chat)
            return $this->responsData(['status' => Response::HTTP_OK, 'chat' => $chat->getId()]);
        else
            return $this->responsData(['status' => Response::HTTP_OK, 'chat' => $ch->getId()]);
    }
}
