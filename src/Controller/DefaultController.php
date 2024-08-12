<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Snappy\Pdf; // این خط را اضافه کنید

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('base.front.twig', [
            'controller_name' => 'HomeController',

        ]);
    }

    #[Route('/test-pdf', name: 'test_pdf')]
    public function generatePdf(Pdf $knpSnappyPdf): Response
    {
        // محتوای HTML ساده برای تست
        $html = '
            <!DOCTYPE html>
            <html dir="rtl" lang="fa">
            <head>
                <meta charset="UTF-8">
                <title>تست PDF</title>
                <style>
                    body {
                        font-family: "DejaVu Sans", sans-serif;
                    }
                </style>
            </head>
            <body>
                <h1>سلام دنیا</h1>
                <p>این یک متن تستی برای بررسی پشتیبانی از زبان فارسی است.</p>
            </body>
            </html>
        ';

        // تولید فایل PDF
        $pdfContent = $knpSnappyPdf->getOutputFromHtml($html);

        // بازگرداندن PDF به عنوان پاسخ HTTP
        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="test.pdf"',
            ]
        );
    }

}
