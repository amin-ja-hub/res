<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('base.front.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/articles', name: 'articles_show')]
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

    #[Route('/articles/{url}', name: 'article_show')]
    public function article(EntityManagerInterface $entityManager, string $url): Response
    {
        // Fetch the article using the URL
        $article = $entityManager->getRepository(\App\Entity\Article::class)->findOneBy(['url' => $url]);

        // If no article is found, return a 404 response
        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        // Render the article template with the article data
        return $this->render('default/front/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/products', name: 'products_show')]
    public function Products(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager
            ->getRepository(\App\Entity\Product::class) // Fully qualified class name
            ->findBy(['published' => 1]); // Find products where 'published' is 1

        return $this->render('default/front/product/list.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/products/{url}', name: 'product_show')]
    public function product(EntityManagerInterface $entityManager, string $url): Response
    {
        // Fetch the product using the URL
        $product = $entityManager->getRepository(\App\Entity\Product::class)->findOneBy(['url' => $url]);

        // If no product is found, return a 404 response
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        // Render the product template with the product data
        return $this->render('default/front/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/article/{item}/{itemtitle}', name: 'articles_by_item')]
    public function findArticlesByItem(string $item, string $itemtitle, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Pagination parameters
        $page = $request->query->getInt('page', 1); // Get current page, default to 1
        $limit = $request->query->getInt('limit', 10); // Get limit per page, default to 10
        $offset = ($page - 1) * $limit;

        // Initialize QueryBuilder
        $queryBuilder = $entityManager->getRepository(\App\Entity\Article::class)->createQueryBuilder('a');
        
        // Conditional query based on the `item` parameter
        switch ($item) {
            case 'category':
                $queryBuilder->innerJoin('a.category', 'c')
                    ->where('c.title = :itemtitle')
                    ->setParameter('itemtitle', $itemtitle);
                break;

            case 'barchasb':
                $queryBuilder->innerJoin('a.barchasbs', 'b') // Adjust the association based on your entity
                    ->where('b.title = :itemtitle')
                    ->setParameter('itemtitle', $itemtitle);
                break;

            // Add other cases as needed

            default:
                throw $this->createNotFoundException('Invalid item type.');
        }

        // Apply pagination
        $query = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        // Render the result
        return $this->render('default/front/article/list.html.twig', [
            'articles' => $paginator,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }


}
