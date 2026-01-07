<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardUsersController extends AbstractController
{
    #[Route('/dashboard/users', name: 'app_dashboard_users')]
    public function index(UserRepository $ur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $ur->findAll();

        return $this->render('dashboard_user/index.html.twig', [
            'users' => $users,
        ]);
    }
}
