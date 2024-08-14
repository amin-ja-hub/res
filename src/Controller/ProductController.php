<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Barchasb;
use App\Service\Service;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/admin/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager
            ->getRepository(Product::class)
            ->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,Service $service): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        // Initialize $barchasbs variable
        $barchasbs = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            // Handle required fields like title, publish, metadesc, text, etc.
            $product->setTitle($formData['title']);
            $product->setPublished($formData['publish']);
            $product->setMetadesc($formData['metadesc']);
            $product->setText($formData['text']);
            $product->setCdate(new \DateTime());
            $product->setType('1');
            $product->setUrl($formData['url']);
            $product->setPrice($formData['price']);
            $product->setOrder($formData['order']);
            // Handle optional fields like category
            if (isset($formData['category'])) {
                $category = $entityManager->getRepository('App\Entity\Category')->find($formData['category']);
                $product->setCategory($category);
            }

            // Check if 'keywords' exists in $formData
            if (isset($formData['keywords'])) {
                $tags = $formData['keywords'];

                // Handle tags (keywords)
                foreach ($tags as $tagName) {
                    $barchasbRepository = $entityManager->getRepository(Barchasb::class);
                    $barchasb = $barchasbRepository->findOneBy(['title' => $tagName]);

                    if (!$barchasb) {
                        $barchasb = new Barchasb();
                        $barchasb->setTitle($tagName);
                        $barchasb->setCdate(new \DateTime());
                        $barchasb->setPublished(1);
                        $barchasb->setType(1);
                        $entityManager->persist($barchasb);
                    }

                    $product->addBarchasb($barchasb);
                }
            }

            // Persist the product entity after handling keywords and file upload
            $entityManager->persist($product);
            $entityManager->flush();
            $entityId = $product->getId();
            if ($request->files->get('file') != null) {
                $file = $request->files->get('file');
                $fileId = $service->uploadFile(3, $file, $entityId, 'mainpic');
                $file = $entityManager->getRepository('App\Entity\File')->find($fileId);
                $product->setImage($file);
                $entityManager->persist($product);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        // Fetch Barchasb entities where type = 1
        $barchasbs = $entityManager->getRepository(Barchasb::class)->findBy(['type' => 1]);

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'barchasbs' => $barchasbs,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
