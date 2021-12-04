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
            if ($user == null) {
                $data = [
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'errors' => "Token invalid",
                ];
                return $this->response($data, [Response::HTTP_UNAUTHORIZED]);
            }
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $todo = new Todo();
            $todo->setUser($user->getId());
            $todo->setTitle($request->get('title'));
            $todo->setDescription($request->get('description'));
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
    public function getTodo(Request $request, UserRepository $userRepository, TodoRepository $todoRepository): JsonResponse
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
        $todos = $todoRepository->findBy(["user_id" => $user->getId()]);

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
                'user_id' => $todo->getUser(),
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
     * @Route("/put/{id}", name="todo_put", methods={"PUT"})
     */
    public function updateUser(Request $request, UserRepository $userRepository, TodoRepository $todoRepository, $id): JsonResponse
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
            $todo = $todoRepository->find($id);
            $entityManager = $this->getDoctrine()->getManager();

            $request = $this->transformJsonBody($request);

            $title = $request->get('title');
            $description = $request->get('description');

            if(!empty($title)){
                $todo->setTitle($title);
            }
            if(!empty($description)){
                $todo->setDescription($description);
            }

            $entityManager->flush();
            $data = [
                'status' => Response::HTTP_OK,
                'errors' => "Todo updated successfully",
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
     * @Route("/delete/{id}", name="todo_delete", methods={"DELETE"})
     */
    public function deleteTodo(Request $request, UserRepository $userRepository, TodoRepository $todoRepository, $id): JsonResponse
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
        $todo = $todoRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();

        if (!$todo) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Todo not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $entityManager->remove(todo);
        $entityManager->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "Todo deleted successfully",
        ];

        //return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        return $this->response($data, []);
    }
}