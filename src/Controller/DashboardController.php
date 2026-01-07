<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(CategoryRepository $cr, UserRepository $ur): Response
    {
        $has_access = $this->isGranted('ROLE_PUBLISHER');

        if (!$has_access) {
            return $this->redirectToRoute('event_index');
        }

        $amountEvents = $this->getUser()->getEvents();
        $amountEvents = sizeof($amountEvents);

        $amountCategories = $cr->findAll();
        $amountCategories = sizeof($amountCategories);

        $amountUsers = $ur->findAll();
        $amountUsers = sizeof($amountUsers);

        return $this->render('dashboard/index.html.twig', [
            'events' => $amountEvents,
            'categories' => $amountCategories,
            'users' => $amountUsers,
        ]);
    }
}
