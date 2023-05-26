<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(User::class);
       return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'listUsers' => $repository->findAll(),
        ]);
    }

    #[Route('/admin/delete/{id}' , name: 'app_admin_delete')]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(EntityManagerInterface $entityManager, $id): Response
    {
        $repository = $entityManager->getRepository(User::class);
        $user = $repository->findByEmail($id)[0];
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'User deleted successfully');
        return $this->redirectToRoute('app_admin');
    }
}
