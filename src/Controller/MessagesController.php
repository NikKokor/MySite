<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Messages;
use App\Entity\User;
use App\Repository\ChatRepository;
use App\Repository\MessagesRepository;
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
* Class MessegesController
* @package App\Controller
* @Route("/messages")
*/
class MessagesController extends ApiController
{
    /**
     * @Route("/add", name="messages_add", methods={"POST"})
     */
    public function addMessages(Request $request, UserRepository $userRepository): JsonResponse
    {
        try {
            $token = $request->headers->get('Token');
            $user = $userRepository->findOneBy(["password" => $token]);
            if ($user == null) {
                return $this->responsStatus(Response::HTTP_UNAUTHORIZED, "Token invalid", [Response::HTTP_UNAUTHORIZED]);
            }
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $messages = new Messages();
            $messages->setUser($user->getId());
            $messages->setChat($request->get('chat_id'));
            $messages->setMessage($request->get('message'));
            $entityManager->persist($messages);
            $entityManager->flush();

            return $this->responsStatus(Response::HTTP_OK, "Message added successfully");
        } catch (\Exception $e) {
            return $this->responsStatus(Response::HTTP_UNPROCESSABLE_ENTITY, "Data no valid", [Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/get", name="message_get", methods={"POST"})
     */
    public function getMessage(Request $request, UserRepository $userRepository, MessagesRepository $messagesRepository, ChatRepository $chatRepository): JsonResponse
    {
        $token = $request->headers->get('Token');
        $user = $userRepository->findOneBy(["password" => $token]);
        if ($user == null) {
            return $this->responsStatus(Response::HTTP_UNAUTHORIZED, "Token invalid", [Response::HTTP_UNAUTHORIZED]);
        }
        $request = $this->transformJsonBody($request);
        $chat = $chatRepository->find($request->get('chat_id'));
        $mess1 = $messagesRepository->findBy(["user" => $user->getId(), "chat" => $chat->getId()]);
        $mess2 = $messagesRepository->findBy(["user" => $chat->getUser2(), "chat" => $chat->getId()]);

        if ($mess1 == $mess2) {
            $mess2 = $messagesRepository->findBy(["user" => $chat->getUser1(), "chat" => $chat->getId()]);
        };

        $message1 = [];
        $message2 = [];

        $i = 0;
        foreach ($mess1 as $mess) {
            $message1[$i]['id'] = $mess->getId();
            $message1[$i]['user'] = $mess->getUser();
            $message1[$i]['chat'] = $mess->getChat();
            $message1[$i]['message'] = $mess->getMessage();
            $i++;
        };

        $i = 0;
        foreach ($mess2 as $mess) {
            $message2[$i]['id'] = $mess->getId();
            $message2[$i]['user'] = $mess->getUser();
            $message2[$i]['chat'] = $mess->getChat();
            $message2[$i]['message'] = $mess->getMessage();
            $i++;
        };

        $i = 0;
        $j = 0;
        $messages = [];
        while ($i < count($message1) || $j < count($message2)):
           if ($i == count($message1)) {
               $messages[] = $message2[$j];
               $j++;
           } elseif ($j == count($message2)) {
               $messages[] = $message1[$i];
               $i++;
           } elseif ($message1[$i]["id"] < $message2[$j]["id"]) {
               $messages[] = $message1[$i];
               $i++;
           } elseif ($message1[$i]["id"] > $message2[$j]["id"]) {
               $messages[] = $message2[$j];
               $j++;
           }
        endwhile;

        if (!$messages) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Messages not found",
            ];
            return $this->responsData([$data, $messages], [Response::HTTP_NOT_FOUND]);
        }

        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "Success",
        ];
        return 	$this->responsData([$data, $messages]);
    }
}
