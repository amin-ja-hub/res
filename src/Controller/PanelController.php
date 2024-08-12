<?php

// src/Controller/PanelController.php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Comment; // Ensure you have the correct namespace
use App\Service\Service;
#[Route('/admin')]
class PanelController extends AbstractController
{
    #[Route('/update-field', name: 'update_field', methods: ['POST'])]
    public function updateField(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $tableName = $request->request->get('table'); // Retrieve 'table' from POST request
        $fieldName = $request->request->get('field'); // Retrieve 'field' from POST request
        $fieldValue = $request->request->get('value'); // Retrieve 'value' from POST request
        $id = $request->request->get('id'); // Retrieve 'id' from POST request

        try {
            // Example: Check if the table name corresponds to a valid entity
            $entityClass = 'App\Entity\\' . ucfirst($tableName);
            if (!class_exists($entityClass)) {
                throw new \Exception('Entity class not found for table: ' . $tableName);
            }

            // Retrieve the entity manager and repository
            $repository = $em->getRepository($entityClass);

            // Find the entity by its primary key (id)
            $entity = $repository->find($id);

            if (!$entity) {
                throw new \Exception('Entity not found for id: ' . $id);
            }

            // Check if the field exists in the entity
            $entityMetadata = $em->getClassMetadata($entityClass);
            if (!$entityMetadata->hasField($fieldName)) {
                throw new \Exception("Field '{$fieldName}' does not exist in entity '{$entityClass}'");
            }

            // Update the specified field
            $setterMethod = 'set' . ucfirst($fieldName); // Assuming convention where setter is set<Field>
            $entity->$setterMethod($fieldValue);

            $entity->setUdate(new \DateTime());

            // Persist changes
            $em->persist($entity);
            $em->flush();

            // Return success response
            return new JsonResponse(['message' => 'Field updated successfully'], 200);
        } catch (\Exception $e) {
            // Return error response
            return new JsonResponse(['error' => 'Failed to update field: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/add-commet', name: 'add_comment', methods: ['POST'])]
    public function addCommentAction(Request $request,EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $commentId = $request->request->get('comment_id');
        $commentText =  $request->request->get('text'); // Trim and normalize whitespace

        if (empty($commentText)) {
            return new JsonResponse(['success' => false, 'error' => 'Comment cannot be empty'], 400);
        }

        try {
            $relatedEntity = $this->entityManager->getRepository(Comment::class)->find($commentId); // Replace RelatedEntity with your actual entity class

            if (!$relatedEntity) {
                return new JsonResponse(['success' => false, 'error' => 'Related entity not found'], 404);
            }

            $comment = new Comment();
            $comment->setPublished('1');
            $comment->setFullName('مدیر سایت');
            $comment->setCdate(new \DateTime());
            $comment->setText($commentText);
            $comment->setComment($relatedEntity); // Assuming there's a method to set the related entity

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    #[Route('/upload-file', name: 'upload_file', methods: ['POST'])]
    public function uploadFiles(Request $request, Service $service) {
        $type = $request->request->get('type');
        $id = $request->request->get('id');
        $status = $request->request->get('status');
        $files = $request->files->get('files');

        $fileAddresses = $service->uploadFile($type, $files, $id, $status);

        return new JsonResponse(['fileAddresses' => $fileAddresses]);
    }

}