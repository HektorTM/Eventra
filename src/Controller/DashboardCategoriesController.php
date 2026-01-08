<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardCategoriesController extends AbstractController
{
    #[Route('/dashboard/categories', name: 'app_dashboard_categories')]
    public function index(CategoryRepository $cr): Response
    {
        $categories = $cr->findall();

        return $this->render('dashboard_category/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}
