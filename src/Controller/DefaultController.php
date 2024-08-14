<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('base.front.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/products', name: 'products_shoe')]
    public function Products(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager
            ->getRepository(\App\Entity\Product::class) // Fully qualified class name
            ->findBy(['published' => 1]); // Find products where 'published' is 1

        return $this->render('default/front/product/list.html.twig', [
            'products' => $products,
        ]);
    }

}
