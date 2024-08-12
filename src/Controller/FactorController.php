<?php

namespace App\Controller;

use App\Entity\Factor;
use App\Form\FactorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/factor')]
class FactorController extends AbstractController
{
    #[Route('/', name: 'app_factor_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $factors = $entityManager
            ->getRepository(Factor::class)
            ->findAll();

        return $this->render('factor/index.html.twig', [
            'factors' => $factors,
        ]);
    }

    #[Route('/new', name: 'app_factor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $factor = new Factor();
        $form = $this->createForm(FactorType::class, $factor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($factor);
            $entityManager->flush();

            return $this->redirectToRoute('app_factor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('factor/new.html.twig', [
            'factor' => $factor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_factor_show', methods: ['GET'])]
    public function show(Factor $factor): Response
    {
        return $this->render('factor/show.html.twig', [
            'factor' => $factor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_factor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Factor $factor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FactorType::class, $factor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_factor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('factor/edit.html.twig', [
            'factor' => $factor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_factor_delete', methods: ['POST'])]
    public function delete(Request $request, Factor $factor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$factor->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($factor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_factor_index', [], Response::HTTP_SEE_OTHER);
    }
}
