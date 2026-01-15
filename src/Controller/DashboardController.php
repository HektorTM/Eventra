<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CreateCategoryType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/dashboard/categories', name: 'app_dashboard_categories')]
    public function categories(CategoryRepository $cr): Response
    {
        $categories = $cr->findall();

        return $this->render('dashboard_category/index.html.twig', [
            'categories' => $categories,
            'showModal' => true,
        ]);
    }

    #[Route('/dashboard/categories/new', name: 'app_dashboard_categories_new')]
    public function newCategory(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $category = new Category();

        $form = $this->createForm(CreateCategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setName($form->get('name')->getData());

            $em->persist($category);
            $em->flush();
        }

        return $this->render('dashboard_events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard/users', name: 'app_dashboard_users')]
    public function users(UserRepository $ur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $ur->findAll();

        return $this->render('dashboard_user/index.html.twig', [
            'users' => $users,
        ]);
    }
}
