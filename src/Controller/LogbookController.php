<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Books;
use App\Entity\Logbook;
use App\Form\LogbookAdd;
use App\Repository\LogbookRepository;
use App\Repository\BookRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Driver\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class LogbookController
 * @package App\Controller
 * @Route("/logbook")
 */
class LogbookController extends ApiController
{
    /**
     * @Route("/", name="logbook_index", methods={"GET"})
     */
    public function index(LogbookRepository $logbookRepository): Response
    {
        return $this->render('logbook/index.html.twig', [
            'logbooks' => $logbookRepository->findAll(),
        ]);
    }

    /**
     * @Route("/add", name="add_record", methods={"POST", "GET"})
     */
    public function addRecord(Request $request) : Response
    {
        $logbook = new Logbook();
        $form = $this->createForm(LogbookAdd::class, $logbook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $logbook->setDateTake(new DateTime());
            $entityManager->persist($logbook);
            $entityManager->flush();

            return $this->redirectToRoute('logbook_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('logbook/add.html.twig', [
            'logbook' => $logbook,
            'form' => $form,
        ]);

//        try {
//            $request = $this->transformJsonBody($request);
//            $entityManager = $this->getDoctrine()->getManager();
//            $record = new Logbook();
//            $record->setBook($request->get('book_id'));
//            $record->setUser($request->get('user_id'));
//            $record->setDateTake(new DateTime());
//
//            $entityManager->persist($record);
//            $entityManager->flush();
//
//            $data = [
//                'status' => Response::HTTP_OK,
//                'success' => "Record added successfully",
//            ];
//            return $this->response($data,[]);
//        }
//        catch (\Exception $e) {
//            $data = [
//                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
//                'errors' => "Data no valid",
//            ];
//            return $this->response($data,[Response::HTTP_UNPROCESSABLE_ENTITY]);
//        }
    }

    /**
     * @Route("/get/{id}", name="record_get_by_id_user", methods={"GET"})
     */
    public function getRecordsByID(UserRepository $userRepository, BookRepository $bookRepository, LogbookRepository $logbookRepository, $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "User not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $records = $logbookRepository->findBy(["user_id" => $id]);
        $arrayRecords = [];

        foreach ($records as $record){
            $book = $bookRepository->find($record->getBook());

            $recordData = array(
                'id' => $record->getId(),
                'user' => [
                    'id' => $user->getId(),
                    'login' => $user->getLogin()
                ],
                'book' => [
                    'id' => $book->getId(),
                    'name' => $book->getName(),
                    'author' => $book->getAuthor(),
                    'year' => $book->getYear()
                ],
                'date_take' => $record->getDateTake(),
                'date_return' => $record->getDateReturn()
            );
            $arrayRecords[] = $recordData;
        }

        if (!$arrayRecords) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Records not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        return $this->response($arrayRecords,[]);
    }

    /**
     * @Route("/get_all", name="record_get_all", methods={"GET"})
     */
    public function getRecords(UserRepository $userRepository, BookRepository $bookRepository, LogbookRepository $logbookRepository): JsonResponse
    {
        $records = $logbookRepository->findAll();
        $arrayRecords = [];

        foreach ($records as $record){
            $book = $bookRepository->find($record->getBook());
            $user = $userRepository->find($record->getUser());

            $recordData = array(
                'id' => $record->getId(),
                'user' => [
                    'id' => $user->getId(),
                    'login' => $user->getLogin()
                ],
                'book' => [
                    'id' => $book->getId(),
                    'name' => $book->getName(),
                    'author' => $book->getAuthor(),
                    'year' => $book->getYear()
                ],
                'date_take' => $record->getDateTake(),
                'date_return' => $record->getDateReturn()
            );
            $arrayRecords[] = $recordData;
        }

        if (!$arrayRecords) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Records not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        return $this->response($arrayRecords,[]);
    }

    /**
     * @Route("/delete/{id}", name="record_delete", methods={"DELETE"})
     */
    public function deleteRecord(LogbookRepository $logbookRepository, $id): JsonResponse
    {
        $record = $logbookRepository->find($id);

        if (!$record) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Record not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($record);
        $entityManager->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "Record deleted successfully",
        ];
        return $this->response($data,[]);
    }

    /**
     * @Route("/return/{user_id?}/{book_id?}", name="return_book", methods={"PUT", "GET"})
     */
    public function returnBook(Request $request, LogbookRepository $logbookRepository, $user_id, $book_id) : JsonResponse
    {
        if($user_id != null && $book_id == null) {
            $record = $logbookRepository->findOneBy([
                "user_id" => $user_id,
                "book_id" => $book_id
            ]);
            $entityManager = $this->getDoctrine()->getManager();

            if (!$record) {
                $data = [
                    'status' => Response::HTTP_NOT_FOUND,
                    'errors' => "Record not found",
                ];
                return $this->response($data, [Response::HTTP_NOT_FOUND]);
            }

            $record->setDateReturn(new \DateTime());

            $entityManager->flush();
            $data = [
                'status' => Response::HTTP_OK,
                'errors' => "Book returned successfully",
            ];
            return $this->redirectToRoute('logbook_index', [], Response::HTTP_SEE_OTHER);
        }
        else {
            try {
                $request = $this->transformJsonBody($request);

                $user_id = $request->get('user_id');
                $book_id = $request->get('book_id');

                $record = $logbookRepository->findOneBy([
                    "user_id" => $user_id,
                    "book_id" => $book_id
                ]);
                $entityManager = $this->getDoctrine()->getManager();

                if (!$record) {
                    $data = [
                        'status' => Response::HTTP_NOT_FOUND,
                        'errors' => "Record not found",
                    ];
                    return $this->response($data, [Response::HTTP_NOT_FOUND]);
                }

                $record->setDateReturn(new \DateTime());

                $entityManager->flush();
                $data = [
                    'status' => Response::HTTP_OK,
                    'errors' => "Book returned successfully",
                ];
                return $this->response($data, []);
            } catch (\Exception $e) {
                $data = [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errors' => "Data no valid",
                ];
                return $this->redirectToRoute('logbook_index', [], Response::HTTP_SEE_OTHER);
            }
        }
    }
}
