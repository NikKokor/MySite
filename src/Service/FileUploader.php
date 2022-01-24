<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    /**
     * @throws Exception
     */
    public function upload(UploadedFile $file): array
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename;
        $fileType = $file->guessExtension();

        try {
            $file->move($this->targetDirectory, $fileName . '.' . $fileType);
        } catch (FileException $e) {
            throw new Exception("Can't move file to directory");
        }

        return [
            'name' => $fileName,
            'type' => $fileType];
    }
}
