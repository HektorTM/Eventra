<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $em,
        private RouterInterface $router,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return 'connect_google_check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $googleUser = $client->fetchUser();

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();

        return new SelfValidatingPassport(
            new UserBadge($email, function () use ($googleId, $email, $googleUser) {
                $repo = $this->em->getRepository(User::class);

                // 1. Google ID first
                $user = $repo->findOneBy(['googleId' => $googleId]);

                // 2. Email fallback (link accounts)
                if (!$user) {
                    $user = $repo->findOneBy(['email' => $email]);

                    if ($user) {
                        $user->setGoogleId($googleId);
                        $this->em->flush();

                        return $user;
                    }
                }

                // 3. Create new user
                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setGoogleId($googleId);
                    $user->setPassword('');
                    $user->setRoles(['ROLE_USER']);
                    $user->setIsActive(true);
                    $user->setIsVerified(true);
                    $user->setCreatedAt(new \DateTimeImmutable());

                    $profile = new UserProfile();
                    $profile->setUser($user);
                    $profile->setAvatarUrl($googleUser->getAvatar());
                    $profile->setDisplayName($googleUser->getName());

                    $user->setUserProfile($profile);
                    $this->em->persist($profile);
                    $this->em->persist($user);
                    $this->em->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return new Response('', 302, [
            'Location' => $this->router->generate('event_index'),
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Google authentication failed', 403);
    }
}
