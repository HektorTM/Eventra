<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\EditProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(UserRepository $ur): Response
    {
        $user = $ur->find($this->getUser()->getId());
        $events = $user->getSavedEvents();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'upcomingEvents' => $events,
        ]);
    }

    #[Route('/account/events', name: 'app_account_events')]
    public function events(UserRepository $ur): Response
    {
        $user = $ur->find($this->getUser()->getId());
        $events = $user->getSavedEvents();

        return $this->render('account/events.html.twig', [
            'user' => $user,
            'events' => $events,
        ]);
    }

    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isVerified()) {
            return $this->redirectToRoute('app_account');
        }

        $form = $this->createForm(EditProfileType::class, $user->getUserprofile());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/password', name: 'app_account_password')]
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();

            $user->setPassword(
                $passwordHasher->hashPassword($user, $newPassword)
            );
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Password changed successfully.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
