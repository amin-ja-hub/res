<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('base.front.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/articles', name: 'article_show')]
    public function Articles(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 1;
        $offset = ($page - 1) * $limit;

        $query = $entityManager
            ->getRepository(\App\Entity\Article::class)
            ->createQueryBuilder('p')
            ->where('p.published = :published')
            ->setParameter('published', 1)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        return $this->render('default/front/article/list.html.twig', [
            'articles' => $paginator,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    #[Route('/products', name: 'product_show')]
    public function Products(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 1;
        $offset = ($page - 1) * $limit;

        $query = $entityManager
            ->getRepository(\App\Entity\Product::class)
            ->createQueryBuilder('p')
            ->where('p.published = :published')
            ->setParameter('published', 1)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        return $this->render('default/front/product/list.html.twig', [
            'products' => $paginator,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }
}
