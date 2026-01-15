<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager,
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsActive(false);
            $user->setIsVerified(false);

            $profile = new UserProfile();
            $profile->setUser($user);

            $displayname = explode('@', $user->getEmail())[0];
            $profile->setDisplayName(str_replace(['_', '.'], ' ', $displayname));
            $profile->setAvatarUrl('https://ui-avatars.com/api/?name=' . $displayname);

            $user->setUserProfile($profile);

            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from('no-reply@evenetra.com')
                ->to($user->getEmail())
                ->subject('Confirm your email')
                ->htmlTemplate('registration/confirmation_email.html.twig');

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                $email
            );

            $this->addFlash('success', 'Please check your email to confirm your address.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/resend-verification', name: 'app_resend_verification')]
    public function resendVerification(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if ($user->isVerified()) {
            return $this->redirectToRoute('dashboard');
        }

        $email = (new TemplatedEmail())
            ->from('no-reply@eventra.com')
            ->to($user->getEmail())
            ->subject('Confirm your email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            $email
        );

        $this->addFlash('success', 'Verification email sent. Please check your inbox.');

        return $this->redirectToRoute('app_verify_notice');
    }

    #[Route('/verify/notice', name: 'app_verify_notice')]
    public function verifyNotice(): Response
    {
        return $this->render('registration/verify_notice.html.twig');
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_verify_notice');
        }

        $this->addFlash('success', 'Your email has been verified successfully.');

        return $this->redirectToRoute('app_login');
    }
}
