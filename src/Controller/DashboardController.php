<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(CategoryRepository $cr): Response
    {
        $amountEvents = $this->getUser()->getEvents();
        $amountEvents = sizeof($amountEvents);

        $amountCategories = $cr->findAll();
        $amountCategories = sizeof($amountCategories);

        return $this->render('dashboard/index.html.twig', [
            'events' => $amountEvents,
            'categories' => $amountCategories,
        ]);
    }
}
