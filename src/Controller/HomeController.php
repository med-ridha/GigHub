<?php 
namespace App\Controller;

use App\Entity\Gig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $manager): Response
    {
        $gigRepositry = $manager->getRepository(Gig::class);
        $gigs = $gigRepositry->findAll();
        return $this->render('home/home.html.twig', [
            "gigs" => $gigs
        ]);
    }

}
