<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoRepository;
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
 * Class UserController
 * @package App\Controller
 * @Route("/user")
 */
class UserController extends ApiController
{
    /**
     * @Route("/reg", name="user_reg", methods={"POST"})
     */
    public function regUser(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
            $user = new User();
            $user->setUsername($request->get('username'));
            $user->setPassword($passwordHasher->hashPassword($user, $request->get('password')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->responsStatus(Response::HTTP_OK, "User registry successfully");
        } catch (\Exception $e) {
            return $this->responsStatus(Response::HTTP_UNPROCESSABLE_ENTITY, "Data no valid", [Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/get/{id}", name="user_get", methods={"GET"})
     */
    public function getUserByID(UserRepository $userRepository, TodoRepository $todoRepository, $id): JsonResponse
    {
        $user = $userRepository->find($id);
        $todos = $todoRepository->findBy(["user_id" => $id]);

        $count = 0;
        foreach ($todos as $todo) {
            $count++;
        }

        if (!$user) {
            return $this->responsStatus(Response::HTTP_NOT_FOUND, "User not found", [Response::HTTP_NOT_FOUND]);
        }

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'count Todo' => $count,
        ];

        return $this->responsData($userData);
    }

    /**
     * @Route("/get_me", name="user_get_me", methods={"GET"})
     */
    public function getUserMe(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $token = $request->headers->get('Token');
        $user = $userRepository->findOneBy(["password" => $token]);
        if ($user == null) {
            return $this->responsStatus(Response::HTTP_UNAUTHORIZED, "Token invalid", [Response::HTTP_UNAUTHORIZED]);
        }

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
        ];

        return $this->responsData($userData);
    }

    /**
     * @Route("/get_all", name="user_get_all", methods={"GET"})
     */
    public function getUsers(UserRepository $userRepository, TodoRepository $todoRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $arrayUsers = [];
        foreach ($users as $user) {
            $todos = $todoRepository->findBy(["user_id" => $user->getId()]);

            $count = 0;
            foreach ($todos as $todo) {
                $count++;
            }

            $obj = [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                'count Todo' => $count,
            ];
            $arrayUsers[] = $obj;
        }

        if (!$arrayUsers) {
            return $this->responsStatus(Response::HTTP_NOT_FOUND, "Users not found", [Response::HTTP_NOT_FOUND]);
        }

        return $this->responsData($arrayUsers);
    }

    /**
     * @Route("/put", name="user_put", methods={"PUT"})
     */
    public function updateUser(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $token = $request->headers->get('Token');
            $user = $userRepository->findOneBy(["password" => $token]);
            if ($user == null) {
                return $this->responsStatus(Response::HTTP_UNAUTHORIZED, "Token invalid", [Response::HTTP_UNAUTHORIZED]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $request = $this->transformJsonBody($request);
            $login = $request->get('username');

            if (!empty($request->get('old_password')) && !empty($request->get('new_password'))) {
                $new_password = $request->get('new_password');

                if (!($passwordHasher->isPasswordValid($user, $request->get('old_password')))) {
                    return $this->responsStatus(Response::HTTP_UNPROCESSABLE_ENTITY, "Old password incorrect", [Response::HTTP_UNPROCESSABLE_ENTITY]);
                }
                $user->setPassword($passwordHasher->hashPassword($user, $new_password));
            }
            if (!empty($login)) {
                $user->setUsername($login);
            }

            $entityManager->flush();
            return $this->responsStatus(Response::HTTP_OK, "User updated successfully");
        } catch (\Exception $e) {
            return $this->responsStatus(Response::HTTP_UNPROCESSABLE_ENTITY, "Data no valid", [Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/delete", name="user_delete", methods={"DELETE"})
     */
    public function deleteUser(Request $request, UserRepository $userRepository, TodoRepository $todoRepository): JsonResponse
    {
        $token = $request->headers->get('Token');
        $user = $userRepository->findOneBy(["password" => $token]);
        if ($user == null) {
            return $this->responsStatus(Response::HTTP_UNAUTHORIZED, "Token invalid", [Response::HTTP_UNAUTHORIZED]);
        }
        $todos = $todoRepository->findBy(["user_id" => $user->getId()]);

        $entityManager = $this->getDoctrine()->getManager();
        foreach ($todos as $todo) {
            $entityManager->remove($todo);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->responsStatus(Response::HTTP_OK, "User deleted successfully");
    }
}
