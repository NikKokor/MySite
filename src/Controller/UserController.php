<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Books;
use App\Entity\Logbook;
use App\Form\UserAdd;
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
     * @Route("/add", name="user_add", methods={"POST", "GET"})
     */
    public function addUser(Request $request, UserPasswordHasherInterface $passwordHasher) : Response
    {
        $user = new User();
        $form = $this->createForm(UserAdd::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/add.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/reg", name="user_reg", methods={"POST"})
     */
    public function regUser(Request $request, UserPasswordHasherInterface $passwordHasher) : JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $user = new User();
            $user->setUsername($request->get('login'));
            $user->setPassword($passwordHasher->hashPassword($user, $request->get('password')));

            $entityManager->persist($user);
            $entityManager->flush();

            $data = [
                'status' => Response::HTTP_OK,
                'success' => "User registry successfully",
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
            'login' => $user->getUsername(),
            'count Todo' => $user->getCountTodo(),
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
                "login" => $user->getUsername(),
                'count Todo' => $user->getCountTodo(),
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
            $Token = $this->transformJsonBody($token);
            $user = $userRepository->findBy(["password" => $Token]);
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
            $old_password = $passwordHasher->hashPassword($user, $request->get('old_password'));
            $new_password = $request->get('new_password');

            if (!empty($login)) {
                $user->setLogin($login);
            }
            if (!empty($new_password) && ($user->getPassword() == $old_password)) {
                $user->setPassword($passwordHasher->hashPassword($user, $new_password));
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
     * @Route("/delete/{id}", name="user_delete", methods={"DELETE", "GET"})
     */
    public function deleteUser(Request $request, UserRepository $userRepository, $id, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $userRepository->find($id);
        $request = $this->transformJsonBody($request);

        if (!$user) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "User not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $password = $passwordHasher->hashPassword($request->get('password'));

        if ($password != $user->getPassword()) {
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

        //return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        return $this->response($data, []);
    }
}
