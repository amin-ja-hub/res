<?php

namespace App\Controller;

use App\Entity\UserInformation;
use App\Form\UserInformationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Section;
use App\Service\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Sefareshat;
use App\Entity\Product;

#[Route('/user')]
class UserInformationController extends AbstractController
{
    #[Route('/profile', name: 'app_user_information_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $userInformationRepository = $entityManager->getRepository(UserInformation::class);
        $sefareshatRepository = $entityManager->getRepository(Sefareshat::class);
        $productRepository = $entityManager->getRepository(Product::class);

        // Fetch UserInformation and all products
        $userInformations = $userInformationRepository->findBy(['User' => $userId]);
        $products = $productRepository->findBy(['published' => 1]);

        $purchasedProducts = $sefareshatRepository->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.product IN (:productIds)')
            ->setParameter('user', $user)
            ->setParameter('productIds', [1, 2])
            ->getQuery()
            ->getResult();

        $hasPurchasedProduct1 = false;
        $hasPurchasedProduct2 = false;

        foreach ($purchasedProducts as $purchasedProduct) {
            $productId = $purchasedProduct->getProduct()->getId();
            $hasPurchasedProduct1 |= ($productId == 1);
            $hasPurchasedProduct2 |= ($productId == 2);
        }

        $results = array_map(function($userInformation) use ($products, $hasPurchasedProduct1, $hasPurchasedProduct2, $sefareshatRepository, $user) {
            $completion = min(array_sum(array_map(fn($field) => !empty($field) ? 5 : 0, [
                $userInformation->getFullName(),
                $userInformation->getDesignation(),
                $userInformation->getAddress(),
                $userInformation->getPhone(),
                $userInformation->getEmail(),
                $userInformation->getImage(),
                $userInformation->getName(),
            ])) + array_sum(array_map(function($section) {
                $weights = [
                    'درباره من' => [6, 7],
                    'تجربیات' => [5, 5, 2, 2, 1],
                    'تحصیلات' => [5, 5, 2, 2, 1],
                    'توانایی ها' => [5, 5, 1],
                    'شبکه های اجتماعی' => [5, 5, 1],
                ];
                return array_sum(array_map(fn($weight, $field) => $field ? $weight : 0, $weights[$section->getType()], [
                    $section->getDescription(), $section->getShtype(), $section->getTitle(),
                    $section->getStart(), $section->getEnd(),
                ]));
            }, $userInformation->getSections()->toArray())), 100);  // Convert PersistentCollection to array

            $productPurchases = array_map(function($product) use ($hasPurchasedProduct1, $hasPurchasedProduct2, $sefareshatRepository, $user) {
                return [
                    'product' => $product,
                    'hasPurchased' => $hasPurchasedProduct1 || $product->getId() == 2 ||
                        count($sefareshatRepository->findBy(['user' => $user, 'product' => $product])) > 0,
                ];
            }, $products);

            return compact('userInformation', 'completion', 'hasPurchasedProduct1', 'productPurchases');
        }, $userInformations);

        return $this->render('user_information/index.html.twig', compact('results'));
    }

    #[Route('/information/new/{variable}', defaults: ["variable" => '1'], name: 'app_user_information_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Service $service, string $variable): Response
    {
        $userInformation = new UserInformation();
        $form = $this->createForm(UserInformationType::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $userInformation->setUser($user);
            $userInformation->setProduct($variable);
            $formData = $request->request->all();
            $file = $request->files->get('file');
            $this->handleUserInformationData($userInformation, $formData, $entityManager, $file, $service, true);
            $entityManager->persist($userInformation);
            $entityManager->flush();
            $id = $userInformation->getId();
            $defaultTab = 2; // Set this to 2 or any other value based on your logic
            return $this->redirectToRoute('app_user_information_edit', [
                'id' => $id,
                'Tab' => $defaultTab,
            ]);
        }

        return $this->render('user_information/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/information/res/{id}/{temp}', name: 'app_user_information_show', methods: ['GET'])]
    public function show(UserInformation $userInformation, string $temp, EntityManagerInterface $entityManager, Service $service): Response
    {
        $user = $this->getUser();
        $sefareshatRepository = $entityManager->getRepository(Sefareshat::class);
        $productRepository = $entityManager->getRepository(Product::class);

        // Find all products
        $products = $productRepository->findAll();
        $hasPurchasedProduct = false;

        foreach ($products as $product) {
            // Product with ID 2 is free
            if ($product->getId() == 2 && $product->getHtmlFile() === $temp) {
                $hasPurchasedProduct = true;
                break; // Free product, no need to check further
            }

            // Check if the user has purchased this product
            $purchasedProducts = $sefareshatRepository->createQueryBuilder('s')
                ->where('s.user = :user')
                ->andWhere('s.product = :product')
                ->setParameter('user', $user)
                ->setParameter('product', $product)
                ->getQuery()
                ->getResult();

            // If the user has purchased product ID 1, grant access to all products
            if (count($purchasedProducts) > 0) {
                if ($product->getId() == 1 || $product->getHtmlFile() === $temp) {
                    $hasPurchasedProduct = true;
                    break; // If product ID 1 is purchased, all products are accessible
                }
            }
        }

        $sectionsWithType = [];
        foreach ($userInformation->getSections() as $section) {
            if ($section->getShtype()) {
                $sectionsWithType[] = $section;
            }
        }

        // Determine whether to generate and return a PDF or just render the HTML
        if ($hasPurchasedProduct) {
            $htmlContent = $this->renderView("user_information/show/{$temp}.html.twig", [
                'info' => $userInformation,
                'type' => $sectionsWithType,
            ]);

            // Generate the PDF using PdfService
            $pdfContent = $service->generatePdf($htmlContent);

            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="user_information.pdf"',
                ]
            );
        } else {
            return $this->render("user_information/show/{$temp}.html.twig", [
                'info' => $userInformation,
                'type' => $sectionsWithType,
                'buy' => $hasPurchasedProduct,
            ]);
        }
    }

    #[Route('/information/{id}/edit', name: 'app_user_information_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserInformation $userInformation, Service $service): Response
    {
        $form = $this->createForm(UserInformationType::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();
            $file = $request->files->get('file');
            $this->handleUserInformationData($userInformation, $formData, $entityManager, $file, $service);

            return $this->redirectToRoute('app_user_information_index');
        }

        return $this->render('user_information/edit.html.twig', [
            'form' => $form->createView(),
            'userInformation' => $userInformation,
        ]);
    }

    #[Route('/handle-form-submission', name: 'handle_form_submission', methods: ['GET', 'POST'])]
    public function handleFormSubmission(Request $request, EntityManagerInterface $entityManager, Service $service): JsonResponse
    {
        // Get the data from the POST request
        $formData = $request->request->all(); // Get all regular form fields
        $file = $request->files->get('file');
        $id = $formData['id'];
        $userInformation = $entityManager->getRepository(UserInformation::class)->find($id);

        // Get the uploaded file
        $this->handleUserInformationData($userInformation, $formData, $entityManager, $file, $service);

        return new JsonResponse(['data' => $formData]);
    }

    private function handleUserInformationData(UserInformation $userInformation, array $formData, EntityManagerInterface $entityManager, ?UploadedFile $file, Service $service, bool $isNew = false): void
    {
        $userInformation->setFullName($formData['full_name'] ?? $userInformation->getFullName());
        $userInformation->setDesignation($formData['designation'] ?? $userInformation->getDesignation());
        $userInformation->setAddress($formData['address'] ?? $userInformation->getAddress());
        $userInformation->setPhone($formData['phone'] ?? $userInformation->getPhone());
        $userInformation->setEmail($formData['email'] ?? $userInformation->getEmail());

        // Handle dynamic sections
        $sectionsData = $formData['sections'] ?? [];
        foreach ($userInformation->getSections() as $section) {
            $userInformation->removeSection($section);
            $entityManager->remove($section);
        }

        foreach ($sectionsData as $sectionData) {
            $type = $sectionData['type'] ?? null;

            // If it's a new entry, set shtype as the type, otherwise use the provided shtype or existing one
            $shtype = $isNew ? $type : ($sectionData['shtype'] ?? null);

            $section = new Section();
            $section->setShtype($shtype);
            $section->setType($type);

            switch ($type) {
                case 'تجربیات':
                case 'تحصیلات':
                    $section->setStart($sectionData['starts'] ?? null);
                    $section->setEnd($sectionData['ends'] ?? null);
                    $section->setTitle($sectionData['titles'] ?? null);
                    $section->setDescription($sectionData['descriptions'] ?? null);
                    break;
                case 'توانایی ها':
                case 'شبکه های اجتماعی':
                    $section->setTitle($sectionData['titles'] ?? null);
                    $section->setDescription($sectionData['descriptions'] ?? null);
                    break;
                case 'درباره من':
                    $section->setDescription($sectionData['description'] ?? null);
                    break;
            }

            $userInformation->addSection($section);
            $entityManager->persist($section);
        }

        // Explicitly persist the userInformation entity
        $entityManager->persist($userInformation);

        // Handle file upload
        if ($file !== null) {
            $fileId = $service->uploadFile(5, $file, $userInformation->getId(), 'mainpic');
            $fileEntity = $entityManager->getRepository('App\Entity\File')->find($fileId);
            $userInformation->setImage($fileEntity);
            $entityManager->persist($userInformation);
        }

        $entityManager->flush();
    }

    #[Route('/information/{id}', name: 'app_user_information_delete', methods: ['POST'])]
    public function delete(Request $request, UserInformation $userInformation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userInformation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($userInformation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_information_index', [], Response::HTTP_SEE_OTHER);
    }
}
