<?php

namespace App\Controller;

use App\Entity\SefareshatProduct;
use App\Form\SefareshatProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sefareshat/product')]
class SefareshatProductController extends AbstractController
{
    #[Route('/', name: 'app_sefareshat_product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $sefareshatProducts = $entityManager
            ->getRepository(SefareshatProduct::class)
            ->findAll();

        return $this->render('sefareshat_product/index.html.twig', [
            'sefareshat_products' => $sefareshatProducts,
        ]);
    }

    #[Route('/new', name: 'app_sefareshat_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sefareshatProduct = new SefareshatProduct();
        $form = $this->createForm(SefareshatProductType::class, $sefareshatProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sefareshatProduct);
            $entityManager->flush();

            return $this->redirectToRoute('app_sefareshat_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sefareshat_product/new.html.twig', [
            'sefareshat_product' => $sefareshatProduct,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sefareshat_product_show', methods: ['GET'])]
    public function show(SefareshatProduct $sefareshatProduct): Response
    {
        return $this->render('sefareshat_product/show.html.twig', [
            'sefareshat_product' => $sefareshatProduct,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sefareshat_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SefareshatProduct $sefareshatProduct, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SefareshatProductType::class, $sefareshatProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sefareshat_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sefareshat_product/edit.html.twig', [
            'sefareshat_product' => $sefareshatProduct,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sefareshat_product_delete', methods: ['POST'])]
    public function delete(Request $request, SefareshatProduct $sefareshatProduct, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sefareshatProduct->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sefareshatProduct);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sefareshat_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
