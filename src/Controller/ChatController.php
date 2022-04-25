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
     * @Route("/", name="app_chat")
     */
    public function index(): Response
    {
        return $this->render('chat/main.html.twig', [
            'controller_name' => 'ChatController',
        ]);
    }

    /**
     * @Route("/add", name="chat_add", methods={"POST"})
     */
    public function addChat(Request $request, UserRepository $userRepository): JsonResponse
    {
        try {
            $token = $request->headers->get('Token');
            $user = $userRepository->findOneBy(["password" => $token]);
            if ($user == null) {
                $data = [
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'errors' => "Token invalid",
                ];
                return $this->response($data, [Response::HTTP_UNAUTHORIZED]);
            }
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $chat = new chat();
            $chat->setUser1($user->getId());
            $chat->setUser2($request->get('user_id'));
            $entityManager->persist($chat);
            $entityManager->flush();

            $data = [
                'status' => Response::HTTP_OK,
                'success' => "Chat create successfully",
            ];
            return $this->response($data, []);
        } catch (\Exception $e) {
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ];
            return $this->response($data, [Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/get", name="chat_get", methods={"POST"})
     */
    public function getChat(Request $request, UserRepository $userRepository, ChatRepository $chatRepository): JsonResponse
    {
        $token = $request->headers->get('Token');
        $user = $userRepository->findOneBy(["password" => $token]);
        if ($user == null) {
            $data = [
                'status' => Response::HTTP_UNAUTHORIZED,
                'errors' => "Token invalid",
            ];
            return $this->response($data, [Response::HTTP_UNAUTHORIZED]);
        }
        $request = $this->transformJsonBody($request);
        $chat = $chatRepository->findOneBy(["user_1" => $user->getId(), "user_2" => $request->get('user_id')]);
        $ch = $chatRepository->findOneBy(["user_2" => $user->getId(), "user_1" => $request->get('user_id')]);
        if (!$chat && !$ch) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Chat not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }
        if ($chat) {
            return $this->response(['status' => Response::HTTP_OK, 'chat' => $chat->getId()], []);
        } else {
            return $this->response(['status' => Response::HTTP_OK, 'chat' => $ch->getId()], []);
        }
    }
}
