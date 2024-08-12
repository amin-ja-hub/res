<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Barchasb;
use App\Service\Service;

#[Route('/writer/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager
            ->getRepository(Article::class)
            ->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,Service $service): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        // Initialize $barchasbs variable
        $barchasbs = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            // Handle required fields like title, publish, metadesc, text, etc.
            $article->setTitle($formData['title']);
            $article->setPublished($formData['publish']);
            $article->setMetadesc($formData['metadesc']);
            $article->setText($formData['text']);
            $article->setCdate(new \DateTime());
            $article->setType('1');
            $article->setUrl($formData['url']);

            // Handle optional fields like category
            if (isset($formData['category'])) {
                $category = $entityManager->getRepository('App\Entity\Category')->find($formData['category']);
                $article->setCategory($category);
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

                    $article->addBarchasb($barchasb);
                }
            }

            // Persist the article entity after handling keywords and file upload
            $entityManager->persist($article);
            $entityManager->flush();
            $entityId = $article->getId();
            if ($request->files->get('file') != null) {
                $file = $request->files->get('file');
                $fileId = $service->uploadFile(1, $file, $entityId, 'mainpic');
                $file = $entityManager->getRepository('App\Entity\File')->find($fileId);
                $article->setImage($file);
                $entityManager->persist($article);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        // Fetch Barchasb entities where type = 1
        $barchasbs = $entityManager->getRepository(Barchasb::class)->findBy(['type' => 1]);

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'barchasbs' => $barchasbs,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager, Service $service): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            // Handle required fields like title, publish, metadesc, text, etc.
            $article->setTitle($formData['title']);
            $article->setMetadesc($formData['metadesc']);
            $article->setText($formData['text']);
            $article->setUrl($formData['url']);

            // Handle optional fields like category
            if (isset($formData['category'])) {
                $category = $entityManager->getRepository('App\Entity\Category')->find($formData['category']);
                $article->setCategory($category);
            } else {
                $article->setCategory(null); // Clear category if none selected
            }

            // Handle tags (keywords)
            if (isset($formData['keywords'])) {
                $tags = $formData['keywords'];

                // Clear existing tags to replace with new ones
                foreach ($article->getBarchasbs() as $barchasb) {
                    $article->removeBarchasb($barchasb);
                }

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

                    $article->addBarchasb($barchasb);
                }
            } else {
                // Clear all tags if none provided
                foreach ($article->getBarchasbs() as $barchasb) {
                    $article->removeBarchasb($barchasb);
                }
            }

            // Handle file upload if a new file is provided
            if ($request->files->get('file') != null) {
                $file = $request->files->get('file');
                $fileId = $service->uploadFile(1, $file, $article->getId(), 'mainpic');
                $fileEntity = $entityManager->getRepository('App\Entity\File')->find($fileId);
                $article->setImage($fileEntity);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        // Fetch Barchasb entities where type = 1
        $barchasbs = $entityManager->getRepository(Barchasb::class)->findBy(['type' => 1]);

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'barchasbs' => $barchasbs,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
