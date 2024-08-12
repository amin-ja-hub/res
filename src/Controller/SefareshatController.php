<?php

namespace App\Controller;

use App\Entity\Sefareshat;
use App\Form\SefareshatType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/sefareshat')]
class SefareshatController extends AbstractController
{
    #[Route('/', name: 'app_sefareshat_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $sefareshats = $entityManager
            ->getRepository(Sefareshat::class)
            ->findAll();

        return $this->render('sefareshat/index.html.twig', [
            'sefareshats' => $sefareshats,
        ]);
    }

    #[Route('/new', name: 'app_sefareshat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sefareshat = new Sefareshat();
        $form = $this->createForm(SefareshatType::class, $sefareshat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sefareshat);
            $entityManager->flush();

            return $this->redirectToRoute('app_sefareshat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sefareshat/new.html.twig', [
            'sefareshat' => $sefareshat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sefareshat_show', methods: ['GET'])]
    public function show(Sefareshat $sefareshat): Response
    {
        return $this->render('sefareshat/show.html.twig', [
            'sefareshat' => $sefareshat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sefareshat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sefareshat $sefareshat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SefareshatType::class, $sefareshat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sefareshat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sefareshat/edit.html.twig', [
            'sefareshat' => $sefareshat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sefareshat_delete', methods: ['POST'])]
    public function delete(Request $request, Sefareshat $sefareshat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sefareshat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sefareshat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sefareshat_index', [], Response::HTTP_SEE_OTHER);
    }
}
