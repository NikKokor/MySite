<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Books;
use App\Entity\Logbook;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Driver\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/user", name="nik_api")
 */
class UserController extends ApiController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/add", name="user_add", methods={"POST"})
     */
    public function addUser(Request $request) : JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $user = new Users();
            $user->setLogin($request->get('login'));
            $user->setPassword($request->get('password'));

            $entityManager->persist($user);
            $entityManager->flush();

            $data = [
                'status' => Response::HTTP_OK,
                'success' => "User added successfully",
            ];
            return $this->response($data,[]);
        }
        catch (\Exception $e) {
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ];
            return $this->response($data,[Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/get/{id}", name="user_get", methods={"GET"})
     */
    public function getUserByID(UserRepository $userRepository, $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "User not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $userData = array(
            'id' => $user->getId(),
            'login' => $user->getLogin()
        );

        return $this->response($userData,[]);
    }

    /**
     * @Route("/get_all", name="user_get_all", methods={"GET"})
     */
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $arrayUsers = [];
        foreach ($users as $user){
            $obj = [
                "id" => $user->getId(),
                "login" => $user->getLogin()
            ];
            $arrayUsers[] = $obj;
        }

        if (!$arrayUsers) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Users not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        return $this->response($arrayUsers, []);
    }

    /**
     * @Route("/{id}", name="user_put", methods={"PUT"})
     */
    public function updateUser(Request $request, UserRepository $userRepository, $id): JsonResponse
    {
        try {
            $user = $userRepository->find($id);
            $entityManager = $this->getDoctrine()->getManager();

            if (!$user) {
                $data = [
                    'status' => Response::HTTP_NOT_FOUND,
                    'errors' => "User not found",
                ];
                return $this->response($data, [Response::HTTP_NOT_FOUND]);
            }

            $request = $this->transformJsonBody($request);

            $login = $request->get('login');
            $password = $request->get('password');

            if (!empty($login)) {
                $user->setLogin($login);
            }
            if (!empty($password)) {
                $password->setPassword($password);
            }

            $entityManager->flush();
            $data = [
                'status' => Response::HTTP_OK,
                'errors' => "User updated successfully",
            ];
            return $this->response($data,[]);
        }
        catch (\Exception $e) {
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ];
            return $this->response($data, [Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE", "GET"})
     */
    public function deleteUser(UserRepository $userRepository, $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "User not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "User deleted successfully",
        ];
        return $this->response($data,[]);
    }
}
