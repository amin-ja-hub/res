<?php

namespace App\Controller;

use App\Entity\ContactUs;
use App\Form\ContactUsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/contact')]
class ContactUsController extends AbstractController
{
    #[Route('/', name: 'app_contact_us_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $contactuses = $entityManager
            ->getRepository(ContactUs::class)
            ->findAll();

        return $this->render('contact_us/index.html.twig', [
            'contact' => $contactuses,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_us_show', methods: ['GET'])]
    public function show(ContactUs $contactU): Response
    {
        return $this->render('contact_us/show.html.twig', [
            'contact_u' => $contactU,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_us_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContactUs $contactU, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactUsType::class, $contactU);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contact_us_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact_us/edit.html.twig', [
            'contact_u' => $contactU,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_us_delete', methods: ['POST'])]
    public function delete(Request $request, ContactUs $contactU, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contactU->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contactU);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contact_us_index', [], Response::HTTP_SEE_OTHER);
    }
}
