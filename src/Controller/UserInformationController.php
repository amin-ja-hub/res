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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Twig\Environment;

#[Route('/profile')]
class UserInformationController extends AbstractController
{
    #[Route('/', name: 'app_user_information_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view this page.');
        }

        $userInformationRepo = $entityManager->getRepository(UserInformation::class);
        $sefareshatRepo = $entityManager->getRepository(Sefareshat::class);
        $productRepo = $entityManager->getRepository(Product::class);

        $userInformations = $userInformationRepo->findBy(['User' => $user->getId()]);
        $purchasedProductIds = $this->getPurchasedProductIds($user, $sefareshatRepo);

        $products = $productRepo->findBy(['published' => 1]);

        $results = array_map(fn($info) => $this->prepareUserInfoData($info, $products, $purchasedProductIds), $userInformations);

        return $this->render('user_information/index.html.twig', ['results' => $results]);
    }

    private function getPurchasedProductIds($user, $sefareshatRepo): array
    {
        $queryBuilder = $sefareshatRepo->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->where('s.user = :user')
            ->andWhere('s.paymentStatus = :paymentStatus')
            ->setParameter('user', $user) // Set parameters one by one
            ->setParameter('paymentStatus', 1); // Correctly set paymentStatus

        // Execute the query and get the results
        $purchases = $queryBuilder->getQuery()->getResult();

        // Extract product IDs from the results
        return array_map(fn($purchase) => $purchase->getProduct()->getId(), $purchases);
    }

    private function prepareUserInfoData(UserInformation $userInformation, array $products, array $purchasedProductIds): array
    {
        $completion = $this->calculateCompletionPercentage($userInformation);

        $productPurchases = array_map(
            fn(Product $product) => [
                'product' => $product,
                'hasPurchased' => in_array($product->getId(), $purchasedProductIds),
            ],
            $products
        );

        return [
            'userInformation' => $userInformation,
            'completion' => $completion,
            'productPurchases' => $productPurchases,
        ];
    }

    private function calculateCompletionPercentage(UserInformation $userInformation): int
    {
        $baseFields = [
            $userInformation->getFullName(),
            $userInformation->getDesignation(),
            $userInformation->getAddress(),
            $userInformation->getPhone(),
            $userInformation->getEmail(),
            $userInformation->getImage(),
            $userInformation->getName(),
        ];

        $baseCompletion = array_sum(array_map(fn($field) => !empty($field) ? 5 : 0, $baseFields));

        $sectionWeights = [
            'درباره من' => [6, 7],
            'تجربیات' => [5, 5, 2, 2, 1],
            'تحصیلات' => [5, 5, 2, 2, 1],
            'توانایی ها' => [5, 5, 1],
            'شبکه های اجتماعی' => [5, 5, 1],
        ];

        $sectionCompletion = array_sum(array_map(
            fn($section) => array_sum(array_map(
                fn($weight, $field) => $field ? $weight : 0,
                $sectionWeights[$section->getType()] ?? [],
                [
                    $section->getDescription(), $section->getShtype(), $section->getTitle(),
                    $section->getStart(), $section->getEnd(),
                ]
            )),
            $userInformation->getSections()->toArray()
        ));

        return min($baseCompletion + $sectionCompletion, 100);
    }

    #[Route('/res/{id}/{temp}', name: 'app_user_information_show', methods: ['GET'])]
    public function show(UserInformation $userInformation, string $temp, EntityManagerInterface $entityManager): Response
    {
        $data = $this->getUserInformationData($userInformation, $temp, $entityManager);

        return $this->render("user_information/show/{$temp}.html.twig", [
            'info' => $userInformation,
            'type' => $data['sectionsWithType'],
            'buy' => $data['hasPurchasedProduct'],
        ]);
    }

    #[Route('/pdf/{id}/{temp}', name: 'app_user_information_generate_pdf', methods: ['GET'])]
    public function generatePdf(UserInformation $userInformation,string $temp,EntityManagerInterface $entityManager,Service $service,Environment $twig): Response {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view this page.');
        }

        $sefareshatRepo = $entityManager->getRepository(Sefareshat::class);
        $productRepo = $entityManager->getRepository(Product::class);

        // Check if the user has purchased the specific product associated with $temp
        $hasPurchasedProduct = $this->checkIfUserHasPurchased($user, $temp, $sefareshatRepo, $productRepo);

        if ($hasPurchasedProduct) {
            $data = $this->getUserInformationData($userInformation, $temp, $entityManager);

            // Load the template and generate the PDF content
            $template = $twig->load("user_information/show/{$temp}.html.twig");
            $htmlContent = $template->renderBlock('pdf_content', [
                'info' => $userInformation,
                'type' => $data['sectionsWithType'],
            ]);

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
            // Render a "not purchased" template if the product was not bought by the user
            return $this->render('user_information/not_purchased.html.twig', [
                'info' => $userInformation,
    //            'temp' => $temp,
            ]);
        }
    }

    private function getUserInformationData(UserInformation $userInformation, string $temp, EntityManagerInterface $entityManager): array
    {
        $user = $this->getUser();
        $sefareshatRepo = $entityManager->getRepository(Sefareshat::class);
        $productRepo = $entityManager->getRepository(Product::class);

        $hasPurchasedProduct = $this->checkIfUserHasPurchased($user, $temp, $sefareshatRepo, $productRepo);

        $sectionsWithType = array_filter($userInformation->getSections()->toArray(), fn($section) => $section->getShtype());

        return [
            'hasPurchasedProduct' => $hasPurchasedProduct,
            'sectionsWithType' => $sectionsWithType,
        ];
    }

    private function checkIfUserHasPurchased($user, string $temp, $sefareshatRepo, $productRepo): bool
    {
        // Find the product associated with the given $temp (assuming HTML file name corresponds to a product)
        $product = $productRepo->findOneBy(['htmlFile' => $temp]);

        // If no such product exists, return false
        if (!$product) {
            return false;
        }

        // Check if the user has purchased this specific product with paymentStatus = 1
        $purchase = $sefareshatRepo->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.product = :product')
            ->andWhere('s.paymentStatus = :paymentStatus')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->setParameter('paymentStatus', 1)
            ->getQuery()
            ->getOneOrNullResult();

        return $purchase !== null;
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

        // Fetch the Product entity based on $variable
        $product = $entityManager->getRepository(Product::class)->find($variable);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $userInformation->setProduct($product);
        $userInformation->setCdate(new \DateTime());

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

    #[Route('/information/{id}/edit', name: 'app_user_information_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserInformation $userInformation, Service $service): Response
    {
        $form = $this->createForm(UserInformationType::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userInformation->setUdate(new \DateTime());
            $formData = $request->request->all();
            $file = $request->files->get('file');
            $this->handleUserInformationData($userInformation, $formData, $entityManager, $file, $service);
            var_dump('a');die;
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
