<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Job;

class DefaultController extends AbstractController
{
    private $registryManager;

    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;
    }

    #[Route('/', name: 'home')]
    // la function home() réagis comme une function list() pour obtenir une liste des entreprises qui ont publié des annonces sur le site
    public function home(): Response
    {

        // on récupère le tableau des logos
        $jobController = new JobController($this->registryManager);
        $logoCategories = $jobController->logoCategories;

        // si l'utilisateur est connecté
        if ($this->getUser() !== null) {             

            // si l'utilisateur n'a pas rempli sa fiche candidat
            if (!$this->getUser()->getCompleted()) {
                return $this->redirectToRoute('candidate_fill');
            }
        }
        
        $repository = $this->registryManager->getManager()->getRepository(Job::class);
        $jobs = $repository->findAll();
        return $this->render('default/home.html.twig', [
            'jobs' => $jobs,
            'logoCategories' => $logoCategories
        ]);
    }

   
}
