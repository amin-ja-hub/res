<?php
// src/Service/Service.php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\File;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Knp\Snappy\Pdf;

class Service
{
    private KernelInterface $kernel;
    private $em;
    private $container;
    private Pdf $pdf;

    public function __construct(Pdf $pdf,EntityManagerInterface $em, ContainerInterface $container,KernelInterface $kernel)
    {
        $this->em = $em;
        $this->container = $container;
        $this->kernel = $kernel;
        $this->pdf = $pdf;

    }

    public function generatePdf(string $htmlContent): string
    {
        return $this->pdf->getOutputFromHtml($htmlContent);
    }

    public function getReplayMessages($replay)
    {
        $dql = "SELECT c FROM App\Entity\Comment c WHERE c.comment = :replay ORDER BY c.id ASC";

        $query = $this->em->createQuery($dql)->setParameter('replay', $replay);
        return $query->getResult();
    }

    public function getCategory($type)
    {
        // Base DQL query
        $dql = "SELECT c FROM App\Entity\Category c WHERE c.published = :published";

        // Check if type is provided
        if ($type !== null) {
            // Append the type condition to the DQL if type is not null
            $dql .= " AND c.type = :type";
        }

        // Add ordering
        $dql .= " ORDER BY c.id ASC";

        // Create the query
        $query = $this->em->createQuery($dql);

        // Set the published parameter
        $query->setParameter('published', '1'); // Assuming you want to fetch published categories

        // Set the type parameter if it's not null
        if ($type !== null) {
            $query->setParameter('type', $type);
        }

        // Execute and return the result
        return $query->getResult();
    }

    //type 1 == article
    //type 2 == category
    //type 3 == product
    //type 4 == user
    //type 5 == res
    public function uploadFile($type, $file, $id, $status) {
        $em = $this->em;
        $projectDir = $this->kernel->getProjectDir();

        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload error: ' . $file->getErrorMessage());
        }

        if ($status == 'mainpic') {
            $FileEntity = new File();
            $unic = sha1(microtime());
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->guessExtension();
            $NewName = $unic . '.' . $extension;  // Changed filename to not include type
            $FileEntity->setName($unic);
            $FileEntity->setType($type);  // Store the type information
            $FileEntity->setCdate(new \DateTime());
            $FileEntity->setFormat($extension);
            $FileEntity->setSize($file->getSize());

            switch ($type) {
                case 1:
                    $fileDir = $projectDir . '/public/uploads/article/' . $id;
                    $FileEntity->setPath('/uploads/article/' . $id);
                    $sizes = [
                        ['width' => 770, 'height' => 350],
                        ['width' => 570, 'height' => 300],
                        ['width' => 85, 'height' => 85],
                    ];
                    break;
                case 2:
                    $fileDir = $projectDir . '/public/uploads/category/' . $id;
                    $FileEntity->setPath('/uploads/category/' . $id);
                    $sizes = [
                        ['width' => 419, 'height' => 554],
                        ['width' => 570, 'height' => 300],
                        ['width' => 85, 'height' => 85],
                    ];
                    break;
                case 3:
                    $fileDir = $projectDir . '/public/uploads/product/' . $id;
                    $FileEntity->setPath('/uploads/product/' . $id);
                    $sizes = [
                        ['width' => 770, 'height' => 350],
                        ['width' => 570, 'height' => 300],
                        ['width' => 85, 'height' => 85],
                    ];
                    break;
                case 4:
                    $fileDir = $projectDir . '/public/uploads/user/' . $id;
                    $FileEntity->setPath('/uploads/user/' . $id);
                    $sizes = [
                        ['width' => 300, 'height' => 300],
                        ['width' => 200, 'height' => 200],
                    ];
                    break;
                case 5:
                    $fileDir = $projectDir . '/public/uploads/res/' . $id;
                    $FileEntity->setPath('/uploads/res/' . $id);
                    $sizes = [
                        ['width' => 600, 'height' => 600],
                        ['width' => 300, 'height' => 300],
                    ];
                    break;
                default:
                    throw new \Exception('Invalid file type.');
            }

            if (!is_dir($fileDir)) {
                if (!mkdir($fileDir, 0755, true) && !is_dir($fileDir)) {
                    throw new \Exception('Unable to create the directory: ' . $fileDir);
                }
            }

            // Move the file to the target directory
            $file->move($fileDir, $NewName);

            // Resize the image to multiple sizes
            $this->resizeImage($fileDir . '/' . $NewName, $sizes);

            $em->persist($FileEntity);
            $em->flush();

            return $FileEntity->getId();
        } elseif ($status == 'gallery') {
            $fileAddresses = [];

            foreach ($file as $file) {
                if ($file->getError() !== UPLOAD_ERR_OK) {
                    throw new \Exception('File upload error: ' . $file->getErrorMessage());
                }

                $FileEntity = new File();
                $unic = sha1(microtime());
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->guessExtension();
                $NewName = $unic . '.' . $extension;  // Changed filename to not include type
                $FileEntity->setName($unic);
                $FileEntity->setType($type);  // Store the type information
                $FileEntity->setCdate(new \DateTime());
                $FileEntity->setFormat($extension);
                $FileEntity->setSize($file->getSize());

                $fileDir = $projectDir . '/public/uploads/files';
                $FileEntity->setPath('/uploads/files');

                if (!is_dir($fileDir)) {
                    if (!mkdir($fileDir, 0755, true) && !is_dir($fileDir)) {
                        throw new \Exception('Unable to create the directory: ' . $fileDir);
                    }
                }

                $file->move($fileDir, $NewName);

                $em->persist($FileEntity);
                $em->flush();

                $fileAddresses[] = $FileEntity->getPath() . '/' . $NewName;
            }

            return $fileAddresses;
        }
    }

    private function resizeImage($filePath, $sizes) {
        list($width, $height) = getimagesize($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $image = null;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($filePath);
                break;
            case 'webp':
                $image = imagecreatefromwebp($filePath);
                break;
            default:
                throw new \Exception('Unsupported image type.');
        }

        foreach ($sizes as $size) {
            $resizeWidth = $size['width'];
            $resizeHeight = $size['height'];
            $newImage = imagecreatetruecolor($resizeWidth, $resizeHeight);

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $width, $height);

            $originalFileName = pathinfo($filePath, PATHINFO_FILENAME);
            $newFilePath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . "{$resizeWidth}x{$resizeHeight}_{$originalFileName}.webp";

            imagewebp($newImage, $newFilePath);

            imagedestroy($newImage);
        }

        imagedestroy($image);
    }

    public function findEntitiesWithCriteria(string $entityClass, ?int $count = null, array $criteria = [], string $orderByField = 'id'): array
    {
        $queryBuilder = $this->em->createQueryBuilder();

        $queryBuilder
            ->select('e')
            ->from($entityClass, 'e')
            ->orderBy("e.$orderByField", 'DESC');

        foreach ($criteria as $field => $value) {
            // Check for special conditions
            if (strpos($field, ' !=') !== false) {
                $actualField = str_replace(' !=', '', $field);
                $queryBuilder->andWhere("e.$actualField != :$actualField")
                             ->setParameter($actualField, $value);
            } else {
                $queryBuilder->andWhere("e.$field = :$field")
                             ->setParameter($field, $value);
            }
        }

        if ($count !== null) {
            $queryBuilder->setMaxResults($count);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
