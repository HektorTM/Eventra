<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventCreateFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardEventsController extends AbstractController
{
    #[Route('/dashboard/events', name: 'app_dashboard_events')]
    public function index(): Response
    {
        $events = $this->getUser()->getEvents();

        return $this->render('dashboard_events/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/dashboard/events/{slug}/edit', name: 'app_dashboard_events_edit')]
    public function edit(
        string $slug,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $event = $em->getRepository(Event::class)->findOneBy([
            'slug' => $slug,
            'created_by' => $this->getUser(),
        ]);

        if (!$event) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EventCreateFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setSlug($this->titleToSlug($event->getTitle()));

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_dir'), $filename);
                $event->setImage('/uploads/'.$filename);
            }

            $em->flush();

            return $this->redirectToRoute('app_dashboard_events');
        }

        return $this->render('dashboard_events/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/dashboard/events/{slug}/publish', name: 'app_dashboard_events_publish')]
    public function publish(string $slug, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $event = $em->getRepository(Event::class)->findOneBy([
            'slug' => $slug,
            'created_by' => $this->getUser(),
        ]);
        if (!$event) {
            throw $this->createNotFoundException();
        }
        $event->setIsPublished(true);
        $em->flush();
        return $this->redirectToRoute('app_dashboard_events');
    }

    #[Route('/dashboard/events/{slug}/delete', name: 'app_dashboard_events_delete')]
    public function delete(string $slug, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $event = $em->getRepository(Event::class)->findOneBy([
            'slug' => $slug,
            'created_by' => $this->getUser(),
        ]);
        if (!$event) {
            throw $this->createNotFoundException();
        }
        $em->remove($event);
        $em->flush();
        return $this->redirectToRoute('app_dashboard_events');
    }

    #[Route('/dashboard/events/new', name: 'app_dashboard_events_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $event = new Event();

        $form = $this->createForm(EventCreateFormType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setCreatedBy($this->getUser());
            $event->setSlug($this->titleToSlug($event->getTitle()));

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_dir'), $filename);
                $event->setImage('/uploads/'.$filename);
            }

            $em->persist($event);
            $em->flush();
        }

        return $this->render('dashboard_events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function titleToSlug(string $title): string
    {
        $title = mb_strtolower($title);
        $title = preg_replace('![^\pL\d]+!u', '-', $title);

        return $title;
    }
}
