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
        $page = max(1, $request->query->getInt('page', 1));
        $query = $entityManager->getRepository(\App\Entity\Article::class)
            ->createQueryBuilder('p')
            ->where('p.published = :published')
            ->setParameter('published', 1)
            ->setFirstResult(($page - 1))
            ->setMaxResults(1)
            ->getQuery();

        $paginator = new Paginator($query, true);
        $totalPages = ceil(count($paginator));

        return $this->render('default/front/article/list.html.twig', [
            'articles' => $paginator, // Change 'paginator' to 'articles'
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    #[Route('/articles/{url}', name: 'article_show')]
    public function article(EntityManagerInterface $entityManager, string $url): Response
    {
        $article = $entityManager->getRepository(\App\Entity\Article::class)->findOneBy(['url' => $url])
            ?: throw $this->createNotFoundException('The article does not exist');

        return $this->render('default/front/article/show.html.twig', compact('article'));
    }

    #[Route('/products', name: 'products_show')]
    public function Products(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(\App\Entity\Product::class)->findBy(['published' => 1]);

        return $this->render('default/front/product/list.html.twig', compact('products'));
    }

    #[Route('/products/{url}', name: 'product_show')]
    public function product(EntityManagerInterface $entityManager, string $url): Response
    {
        $product = $entityManager->getRepository(\App\Entity\Product::class)->findOneBy(['url' => $url])
            ?: throw $this->createNotFoundException('The product does not exist');

        return $this->render('default/front/product/show.html.twig', compact('product'));
    }

    #[Route('/{entity}/{item}/{itemtitle}', name: 'articles_by_item')]
    public function findArticlesByItem(string $entity, string $item, string $itemtitle, Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $offset = ($page - 1) * $limit;

        $className = 'App\Entity\\' . $entity;
        $qb = $entityManager->getRepository($className)->createQueryBuilder('a');

        switch ($item) {
            case 'category':
                $qb->innerJoin('a.category', 'c')->where('c.title = :itemtitle')->setParameter('itemtitle', $itemtitle);
                break;
            case 'barchasb':
                $qb->innerJoin('a.barchasbs', 'b')->where('b.title = :itemtitle')->setParameter('itemtitle', $itemtitle);
                break;
            default:
                throw $this->createNotFoundException('Invalid item type.');
        }

        $query = $qb->setFirstResult($offset)->setMaxResults($limit)->getQuery();
        $paginator = new Paginator($query, true);
        $totalPages = ceil(count($paginator) / $limit);

        // Use an associative array to map entities to their respective folder and variable names
        $entityMapping = [
            'Product' => ['folder' => 'product', 'variable' => 'products'],
            'Article' => ['folder' => 'article', 'variable' => 'articles'],
        ];

        if (!isset($entityMapping[$entity])) {
            throw $this->createNotFoundException('Invalid entity type.');
        }

        $folder = $entityMapping[$entity]['folder'];
        $enitem = $entityMapping[$entity]['variable'];

        return $this->render("default/front/{$folder}/list.html.twig", [
            $enitem => $paginator,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }


    #[Route('/search/articles', name: 'articles_search')]
    public function search(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = max($request->query->getInt('page', 1), 1);
        $limit = 10;
        $searchTerm = $request->query->get('search', '');
        $offset = ($page - 1) * $limit;

        $qb = $entityManager->getRepository(\App\Entity\Article::class)->createQueryBuilder('a')
            ->where('a.published = :published')
            ->setParameter('published', 1);

        if ($searchTerm) {
            $qb->andWhere('a.title LIKE :searchTerm OR a.metadesc LIKE :searchTerm')
               ->setParameter('searchTerm', "%$searchTerm%");
        }

        $query = $qb->setFirstResult($offset)->setMaxResults($limit)->getQuery();
        $paginator = new Paginator($query, true);

        return $this->render('default/front/article/list.html.twig', [
            'articles' => $paginator,
            'totalPages' => ceil(count($paginator) / $limit),
            'currentPage' => $page,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/contact-us', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contactUs = new \App\Entity\ContactUs();
        $form = $this->createForm(\App\Form\ContactUsType::class, $contactUs);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $contactUs->setFullName($formData->getFullName())
                      ->setText($formData->getText())
                      ->setEmail($formData->getEmail())
                      ->setType($formData->getType())
                      ->setCdate(new \DateTime());

            $entityManager->persist($contactUs);
            $entityManager->flush();

            $this->addFlash('success', 'Your message was added successfully.');

            return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_homepage'));
        }

        return $this->render('contact_us/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
#[Route('/message/{entity}', name: 'app_add_message', methods: ['POST'])]
public function message(Request $request, EntityManagerInterface $entityManager, string $entity): Response
{
    $formData = $request->request->all();
    $comment = new \App\Entity\Comment();
    $className = 'App\Entity\\' . $entity;

    // Retrieve the entity instance based on the provided entity type
    $entityInstance = $entityManager->getRepository($className)->find($formData['article'] ?? $formData['product']);

    $comment->setFullName($formData['fullName'])
        ->setText($formData['text'])
        ->setPhone($formData['email'])
        ->setType($formData['type'])
        ->setCdate(new \DateTime());

    // Conditionally set the appropriate relationship
    if ($entity === 'Article') {
        $comment->setArticle($entityInstance);
    } elseif ($entity === 'Product') {
        $comment->setProduct($entityInstance);
    } else {
        throw $this->createNotFoundException('Invalid entity type.');
    }

    $entityManager->persist($comment);
    $entityManager->flush();

    $this->addFlash('success', 'Your message was added successfully.');

    return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_homepage'));
}


}
