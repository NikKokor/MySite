<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Todo;
use App\Form\UserAdd;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Driver\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
* Class TodoController
* @package App\Controller
* @Route("/todo")
*/
class TodoController extends ApiController
{
    /**
     * @Route("/add", name="todo_add", methods={"POST"})
     */
    public function addTodo(Request $request, UserRepository $userRepository): JsonResponse
    {
        try {
            $token = $request->headers->get('Token');
            $user = $userRepository->findOneBy(["password" => $token]);
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $todo = new Todo();
            $todo->setTitle($request->get('title'));
            $todo->setDescription($request->get('description'));
            $user->addTodo($todo);
            $entityManager->persist($todo);
            $entityManager->flush();

            $data = [
                'status' => Response::HTTP_OK,
                'success' => "Todo added successfully",
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
     * @Route("/get", name="todo_get", methods={"GET"})
     */
    public function getTodo(Request $request, UserRepository $userRepository): JsonResponse
    {
        $token = $request->headers->get('Token');
        $user = $userRepository->findOneBy(["password" => $token]);
        $todos = $user->getTodo();

        $arrayTodo = [
            'username' => $user->getUsername()
        ];
        foreach ($todos as $todo) {
            $obj = [
                'id' => $todo->getId(),
                'title' => $todo->getTitle(),
                'description' => $todo->getDescription(),
            ];
            $arrayTodo[] = $obj;
        }

        if (!$arrayTodo) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Todos not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        return $this->response($arrayTodo, []);
    }

    /**
     * @Route("/get_all", name="todo_get_all", methods={"GET"})
     */
    public function getAllTodo(TodoRepository $todoRepository): JsonResponse
    {
        $todos = $todoRepository->findAll();

        $arrayTodo = [];
        foreach ($todos as $todo) {
            $obj = [
                'id' => $todo->getId(),
                'title' => $todo->getTitle(),
                'description' => $todo->getDescription(),
            ];
            $arrayTodo[] = $obj;
        }

        if (!$arrayTodo) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Todos not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        return $this->response($arrayTodo, []);
    }
}