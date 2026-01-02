<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserApiController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getCollection(UserRepository $repo): Response
    {
        return $this->json($repo->findAll());
    }

    #[Route('/{id<\d+>}', methods: ['GET'])]
    public function get(int $id, UserRepository $repo): Response
    {
        $user = $repo->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->json($user);
    }
}
