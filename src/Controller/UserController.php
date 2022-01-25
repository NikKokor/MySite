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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/user")
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
     * @Route("/reg", name="user_reg", methods={"POST"})
     */
    public function regUser(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $user = new User();
            $user->setUsername($request->get('username'));
            $user->setPassword($passwordHasher->hashPassword($user, $request->get('password')));

            $entityManager->persist($user);
            $entityManager->flush();

            $data = [
                'status' => Response::HTTP_OK,
                'success' => "User registry successfully",
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
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "User not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $userData = array(
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'count Todo' => $count,
        );

        return $this->response($userData, []);
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
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Users not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        return $this->response($arrayUsers, []);
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
                $data = [
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'errors' => "Token invalid",
                ];
                return $this->response($data, [Response::HTTP_UNAUTHORIZED]);
            }
            $entityManager = $this->getDoctrine()->getManager();

            if (!$user) {
                $data = [
                    'status' => Response::HTTP_NOT_FOUND,
                    'errors' => "User not found",
                ];
                return $this->response($data, [Response::HTTP_NOT_FOUND]);
            }

            $request = $this->transformJsonBody($request);
            $login = $request->get('username');

            if (!empty($request->get('old_password')) && !empty($request->get('new_password'))) {
                $new_password = $request->get('new_password');

                if ($passwordHasher->isPasswordValid($user, $request->get('old_password'))) {
                    $user->setPassword($passwordHasher->hashPassword($user, $new_password));
                } else {
                    $data = [
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'errors' => "Old password incorrect",
                    ];
                    return $this->response($data, [Response::HTTP_UNPROCESSABLE_ENTITY]);
                }
            }
            if (!empty($login)) {
                $user->setUsername($login);
            }

            $entityManager->flush();
            $data = [
                'status' => Response::HTTP_OK,
                'errors' => "User updated successfully",
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
     * @Route("/delete", name="user_delete", methods={"DELETE"})
     */
    public function deleteUser(Request $request, UserRepository $userRepository, TodoRepository $todoRepository): JsonResponse
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
        $entityManager = $this->getDoctrine()->getManager();

        if (!$user) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "User not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        foreach ($todos as $todo) {
            $entityManager->remove($todo);
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "User deleted successfully",
        ];

        //return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        return $this->response($data, []);
    }
}
