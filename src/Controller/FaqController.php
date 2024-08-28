<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Entity\Category;
use App\Form\FaqType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/admin/faq')]
class FaqController extends AbstractController
{
    #[Route('/', name: 'app_faq_index', methods: ['GET', 'POST'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $faq = new Faq();
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $faq->setCdate(new \DateTime());

            $entityManager->persist($faq);
            $entityManager->flush();
            return $this->redirectToRoute('app_faq_index');
        }

        if ($request->isMethod('POST') && isset($request->request->all()['update'])) {
            $data = $request->request->all();
            $faqId = $data['id'] ?? null;
            $categoryId = $data['category'] ?? null;

            if ($faqId) {
                $faq = $entityManager->getRepository(Faq::class)->find($faqId);

                if ($faq) {
                    $faq->setQuestion($data['question'] ?? $faq->getQuestion());
                    $faq->setAnswer($data['answer'] ?? $faq->getAnswer());
                    $faq->setUdate(new \DateTime());
                    if ($categoryId) {
                        $category = $entityManager->getRepository(Category::class)->find($categoryId);
                        if ($category) {
                            $faq->setCategory($category);
                        } else {
                            $this->addFlash('error', 'Category not found.');
                        }
                    }

                    $entityManager->flush();
                }
            }
        }

        $faqs = $entityManager->getRepository(Faq::class)->findAll();

        return $this->render('faq/index.html.twig', [
            'faqs' => $faqs,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories', name: 'api_categories', methods: ['GET'])]
    public function getCategories(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $categories = $entityManager->getRepository(Category::class)->findBy([
                'published' => 1
            ]);

            $categoryData = array_map(function (Category $category) {
                return [
                    'id' => $category->getId(),
                    'name' => $category->getTitle(), // Ensure this method exists
                ];
            }, $categories);

            return new JsonResponse($categoryData);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], 500);
        }
    }

    #[Route('/{id}', name: 'app_faq_delete', methods: ['POST'])]
    public function delete(Request $request, Faq $faq, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$faq->getId(), $request->request->get('_token'))) {
            $entityManager->remove($faq);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_faq_index');
    }
}
