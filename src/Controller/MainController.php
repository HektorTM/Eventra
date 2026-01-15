<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response
    {
        $events = $em->getRepository(Event::class)->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/events/{id}/join', name: 'app_event_join')]
    public function join(
        string $id,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
    ): Response {
        $userId = $this->getUser()->getId();
        $user = $userRepository->find($userId);
        $event = $em->getRepository(Event::class)->find($id);
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Please verify your email before joining this event.');
            return $this->redirectToRoute(
                $request->query->get('return', 'event_show'),
                ['slug' => $request->query->get('returnSlug')]
            );
        }

        $userId = (int) $request->query->get('userId');

        if ($user->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        if (!$event->getParticipants()->contains($user)) {
            $event->addParticipant($user);
            $user->addSavedEvent($event);
            $em->flush();
        } else {
            $event->removeParticipant($user);
            $user->removeSavedEvent($event);
            $em->flush();
        }

        return $this->redirectToRoute(
            $request->query->get('return', 'app_event_show'),
            ['slug' => $request->query->get('returnSlug')]
        );
    }

    #[Route('/events/{slug}', name: 'app_event_show')]
    public function show(string $slug, EntityManagerInterface $em, UserRepository $ur): Response
    {
        $event = $em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        $isParticipant = $event->getParticipants()->contains($ur->find($this->getUser()->getId()));

        if ($event) {
            return $this->render('event/show.html.twig', [
                'event' => $event,
                'isParticipant' => $isParticipant,
            ]);
        }

        throw $this->createNotFoundException();
    }
}
