<?php

namespace App\Controller;

use App\Entity\CartProduct;
use App\Form\CartProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart/product')]
class CartProductController extends AbstractController
{
    #[Route('/', name: 'app_cart_product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $cartProducts = $entityManager
            ->getRepository(CartProduct::class)
            ->findAll();

        return $this->render('cart_product/index.html.twig', [
            'cart_products' => $cartProducts,
        ]);
    }

    #[Route('/new', name: 'app_cart_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cartProduct = new CartProduct();
        $form = $this->createForm(CartProductType::class, $cartProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cartProduct);
            $entityManager->flush();

            return $this->redirectToRoute('app_cart_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cart_product/new.html.twig', [
            'cart_product' => $cartProduct,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cart_product_show', methods: ['GET'])]
    public function show(CartProduct $cartProduct): Response
    {
        return $this->render('cart_product/show.html.twig', [
            'cart_product' => $cartProduct,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cart_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CartProduct $cartProduct, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CartProductType::class, $cartProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cart_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cart_product/edit.html.twig', [
            'cart_product' => $cartProduct,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cart_product_delete', methods: ['POST'])]
    public function delete(Request $request, CartProduct $cartProduct, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cartProduct->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cartProduct);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cart_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
