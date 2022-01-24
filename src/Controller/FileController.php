<?php

namespace App\Controller;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FileRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;

/**
 * Class FileController
 * @package App\Controller
 * @Route("/file")
 */
class FileController extends ApiController
{
    /**
     * @Route("/file", name="file")
     */
    public function index(): Response
    {
        return $this->render('file/index.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    /**
     * @Route("/add", name="add_file", methods={"POST"})
     */
    public function addFile(Request $request, FileRepository $fileRepository, FileUploader $fileUploader) : JsonResponse
    {
        $fileData = $request->files->get('file');

        if ($fileData) {
            try {
                $sluggedFileData = $fileUploader->upload($fileData);
            } catch (Exception $error) {
                return $this->response([
                    'status' => Response::HTTP_CONFLICT,
                    'errors' => $error->getMessage(),
                ], "403");
            }


            $entityManager = $this->getDoctrine()->getManager();
            $file = new File();
            $file->setName($sluggedFileData['name']);
            $file->setType($sluggedFileData['type']);
            $file->setDirectory($this->getParameter('files_directory'));
            $file->setSize($fileData->getClientSize());

            $entityManager->persist($file);
            $entityManager->flush();

            return $this->response([
                'status' => Response::HTTP_OK,
                'message' => "File was added successfully",
            ], []);
        }
        return $this->response([
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'errors' => "Incorrect data",
        ], [Response::HTTP_UNPROCESSABLE_ENTITY]);
    }

    /**
     * @Route("/get/{name}", name="file_get_by_name", methods={"GET"})
     */
    public function getByName(FileRepository $fileRepository, $name): BinaryFileResponse
    {
        $file = $fileRepository->findOneBy(["name" => $name]);
        if ($file) {
            return $this->binaryResponse($file->getMime());
        }
        return $this->response([
            'status' => Response::HTTP_NOT_FOUND,
            'message' => "No File with that name: " . $name,
        ], [Response::HTTP_NOT_FOUND]);
    }

    /**
     * @Route("/get_all", name="file_get_all", methods={"GET"})
     */
    public function getAll(FileRepository $fileRepository, $name): JsonResponse
    {
        $files = $fileRepository->findAll();
        $data = [];
        if (count($files) > 0) {
            for ($i = 0; $i < count($files); $i++) {
                $data[$i] = $files[$i]->getData();
            }
            return $this->response($data, []);
        }
        return $this->response([
            'status' => Response::HTTP_NOT_FOUND,
            'message' => "No file with that name: " . $name,
        ], [Response::HTTP_NOT_FOUND]);
    }

    /**
    * @Route("/delete/{name}", name="file_delete", methods={"DELETE"})
    */
    public function delete(Request $request, FileRepository $fileRepository, $name): JsonResponse {
        $file = $fileRepository->findOneBy(['name' => $name]);
        if (!$file) {
            return $this->response([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "No file with that name: " . $name,
            ], [Response::HTTP_NOT_FOUND]);
        }
        $filesystem = new Filesystem();
        try {
            $filesystem->remove([$file->getMime()]);
        } catch (IOExceptionInterface $exception) {
            return $this->response([
                'status' => Response::HTTP_METHOD_NOT_ALLOWED,
                'message' => "Can't delete from directory",
            ], [Response::HTTP_METHOD_NOT_ALLOWED]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($file);
        $entityManager->flush();

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => "File was deleted successfully",
        ], []);
    }

    public function binaryResponse($mime): BinaryFileResponse
    {
        return new BinaryFileResponse($mime);
    }
}
