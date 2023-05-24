<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Gig;
use App\Form\FormCreateGigType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class GigController extends AbstractController
{
    #[Route('/gigs', name: 'app_gig')]
    public function index(EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        

        $gigs = $entityManager->getRepository(Gig::class)->findByCreator($user);
        return $this->render('gig/index.html.twig', [
            'gigs' => $gigs,
        ]);
    }

    #[Route('/gigs/create', name: 'app_gigs_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $gig = new Gig();
        $formCreateGig = $this->createForm(FormCreateGigType::class, $gig);

        $formCreateGig->handleRequest($request);
        if ($formCreateGig->isSubmitted() && $formCreateGig->isValid()) {
            $gig = $formCreateGig->getData();
            $gig->setCreator($user);
            $gig->setPosteDate(new \DateTime('now'));

            $entityManager->persist($gig);
            $entityManager->flush();

            $this->addFlash('success', 'Gig created !');
            return $this->redirectToRoute('app_gig');
            
        }

        return $this->render('gig/create.html.twig', [
            'form' => $formCreateGig->createView(), 
        ]);
    }

    #[Route('/gigs/show/{id}', name: 'app_gigs_show_id')]
    public function showId(EntityManagerInterface $entityManager, $id): Response
    {
        $gig = $entityManager->getRepository(Gig::class)->find($id);

        return $this->render('gig/showId.html.twig', [
            'gig' => $gig,
        ]);
    }
}
