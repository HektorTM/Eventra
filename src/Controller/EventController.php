<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/', name: 'event_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $events = $em->getRepository(Event::class)->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/events/{slug}', name: 'event_show')]
    public function show(string $slug, EntityManagerInterface $em): Response
    {
        $event = $em->getRepository(Event::class)->findOneBy(['slug' => $slug]);

        if ($event) {
            return $this->render('event/show.html.twig', [
                'event' => $event,
            ]);
        }

        throw $this->createNotFoundException();


    }
}
