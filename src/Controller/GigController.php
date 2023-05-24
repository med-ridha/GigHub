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
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GigController extends AbstractController
{
    #[Route('/gigs', name: 'app_gigs')]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $gigs = $entityManager->getRepository(Gig::class)->findByCreator($user);
        $AllGigs = $entityManager->getRepository(Gig::class)->findAll();
        for ($i = 0; $i < count($AllGigs); $i++) {
            if (in_array($user->getUserIdentifier(), $AllGigs[$i]->getFreelancers())) {
                $gigsFreelancer[] = $AllGigs[$i];
            }
        }
        return $this->render('gig/index.html.twig', [
            'gigs' => $gigs,
            'gigsFreelancer' => $gigsFreelancer,
        ]);
    }

    #[Route('/gigs/create', name: 'app_gigs_create')]
    #[IsGranted('ROLE_CLIENT')]
    public function create(Request $request, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $gig = new Gig();
        $formCreateGig = $this->createForm(FormCreateGigType::class, $gig);

        $formCreateGig->handleRequest($request);
        if ($formCreateGig->isSubmitted() && $formCreateGig->isValid()) {
            $gig = $formCreateGig->getData();
            $gig->setCreator($user);
            $gig->setState('open');
            $gig->setPosteDate(new \DateTime('now'));

            $entityManager->persist($gig);
            $entityManager->flush();

            $this->addFlash('success', 'Gig created !');
            return $this->redirectToRoute('app_gigs');
        }

        return $this->render('gig/create.html.twig', [
            'form' => $formCreateGig->createView(),
        ]);
    }

    #[Route('/gigs/show/{id}', name: 'app_gigs_show')]
    public function showId(EntityManagerInterface $entityManager, $id): Response
    {
        $gig = $entityManager->getRepository(Gig::class)->find($id);

        return $this->render('gig/show.html.twig', [
            'gig' => $gig,
        ]);
    }

    #[Route('/gigs/edit/{id}', name: 'app_gigs_edit')]
    #[IsGranted('ROLE_CLIENT')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserInterface $user, $id): Response
    {
        $gig = $entityManager->getRepository(Gig::class)->find($id);
        if ($gig->getCreator()->getUserIdentifier() != $user->getUserIdentifier()) {
            $this->addFlash('error', 'You are not the creator of this gig !');
            return $this->redirectToRoute('app_gigs');
        }
        $formCreateGig = $this->createForm(FormCreateGigType::class, $gig);
        $posteDate = $gig->getPosteDate();

        $formCreateGig->handleRequest($request);
        if ($formCreateGig->isSubmitted() && $formCreateGig->isValid()) {
            $gig = $formCreateGig->getData();
            $gig->setPosteDate($posteDate);

            $entityManager->persist($gig);
            $entityManager->flush();

            $this->addFlash('success', 'Gig edited !');
            return $this->redirectToRoute('app_gigs');
        }

        return $this->render('gig/edit.html.twig', [
            'form' => $formCreateGig->createView(),
        ]);
    }
    #[Route('/gigs/delete/{id}', name: 'app_gigs_delete')]
    #[IsGranted('ROLE_CLIENT')]
    public function delete(EntityManagerInterface $entityManager, UserInterface $user, $id): Response
    {
        $gig = $entityManager->getRepository(Gig::class)->find($id);
        if ($gig->getCreator()->getUserIdentifier() != $user->getUserIdentifier()) {
            $this->addFlash('error', 'You are not the creator of this gig !');
            return $this->redirectToRoute('app_gig');
        }
        $entityManager->remove($gig);
        $entityManager->flush();

        $this->addFlash('success', 'Gig deleted !');
        return $this->redirectToRoute('app_gigs');
    }
    #[Route('/gigs/apply/{id}', name: 'app_gigs_apply')]
    #[IsGranted('ROLE_FREELANCER')]
    public function apply(EntityManagerInterface $entityManager, UserInterface $user, $id): Response
    {
        $gig = $entityManager->getRepository(Gig::class)->find($id);
        if ($gig == null) {
            $this->addFlash('error', 'Gig not found !');
            return $this->redirectToRoute('app_gigs');
        }
        if ($gig->getState() == 'accepted') {
            $this->addFlash('error', 'This gig is already accepted !');
            return $this->redirectToRoute('app_gigs');
        }
        if ($gig->getState() == 'done') {
            $this->addFlash('error', 'This gig is already done !');
            return $this->redirectToRoute('app_gigs');
        }
        $gig->addFreelancer($user->getUserIdentifier());
        $entityManager->persist($gig);
        $entityManager->flush();

        $this->addFlash('success', 'You applied to this gig !');
        return $this->redirectToRoute('app_gigs');
    }

    #[Route('/gigs/accept/{id}/{freelancer}', name: 'app_gigs_accept')]
    #[IsGranted('ROLE_CLIENT')]
    public function accept(EntityManagerInterface $entityManager, UserInterface $user, $id, $freelancer): Response
    {
        
        $gig = $entityManager->getRepository(Gig::class)->find($id);
        if ($gig == null) {
            $this->addFlash('error', 'Gig not found !');
            return $this->redirectToRoute('app_gigs');
        }
        if ($gig->getState() == 'accepted') {
            $this->addFlash('error', 'This gig is already accepted !');
            return $this->redirectToRoute('app_gigs');
        }
        if ($gig->getCreator()->getUserIdentifier() != $user->getUserIdentifier()) {
            $this->addFlash('error', 'You are not the creator of this gig !');
            return $this->redirectToRoute('app_gigs');
        }
        $gig->setState('accepted');
        $gig->setFreelancer([$freelancer]);
        $entityManager->persist($gig);
        $entityManager->flush();

        $this->addFlash('success', 'You accepted this freelancer !');
        return $this->redirectToRoute('app_gigs');
    }

    #[Route('/gigs/markdone/{id}', name: 'app_gigs_markdone')]
    #[IsGranted('ROLE_FREELANCER')]
    public function markdone (EntityManagerInterface $entityManager, UserInterface $user, $id): Response
    {
        $gig = $entityManager->getRepository(Gig::class)->find($id);
        if ($gig == null) {
            $this->addFlash('error', 'Gig not found !');
            return $this->redirectToRoute('app_gigs');
        }
        if ($gig->getState() != 'accepted') {
            $this->addFlash('error', 'This gig is not accepted !');
            return $this->redirectToRoute('app_gigs');
        }
        if ($gig->getFreelancer()[0] != $user->getUserIdentifier()) {
            $this->addFlash('error', 'You are not the freelancer of this gig !');
            return $this->redirectToRoute('app_gigs');
        }
        $gig->setState('done');
        $entityManager->persist($gig);
        $entityManager->flush();

        $this->addFlash('success', 'You marked this gig as done !');
        return $this->redirectToRoute('app_gigs');
    }
}
