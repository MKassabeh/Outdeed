<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Candidate;
use App\Entity\ProfessionalExperience;
use App\Entity\Skill;
use App\Entity\User;
use App\Entity\Job;
use App\Entity\Company;

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

            // si l'utilisateur n'a pas rempli les infos nécessaires
            if (!$this->getUser()->getCompleted()) {

                // si je suis candidat
                if($this->getUser()->getUserType() == 'candidate') {

                    // je dois remplir ma fiche candidat
                    return $this->redirectToRoute('candidate_fill');
                }

                // si je suis entreprise
                if($this->getUser()->getUserType() == 'company') {

                    // je dois remplir ma fiche entreprise
                    return $this->redirectToRoute('company_fill');
                }

                
            }
        }
        
        $repository = $this->registryManager->getManager()->getRepository(Job::class);
        $jobs = $repository->findAll();
        return $this->render('default/home.html.twig', [
            'jobs' => $jobs,
            'logoCategories' => $logoCategories
        ]);
    }

    #[Route('/account-candidate', name: 'account_candidate')]
    public function account_candidate(): Response
    {

        // si je suis une entreprise -> redirection
        if ($this->getUser()->getUserType() == 'company') {
            return $this->redirectToRoute('account_company');
        }

        if (!$this->getUser()->getCompleted()) {
            $this->addFlash('info', 'Veuillez entrer vos données personnelles');
            return $this->redirectToRoute('candidate_fill');
        }

        $em = $this->registryManager->getManager();
        $candidate = $em->getRepository(Candidate::class)->findBy(['user' => $this->getUser()]);

        $exps = $em->getRepository(ProfessionalExperience::class)->findBy(['candidate' => $candidate]);
        $skills = $em->getRepository(Skill::class)->findBy(['candidate' => $candidate]);


        return $this->render('candidate/account.html.twig', [
            'candidate' => $candidate[0],
            'experiences' => $exps,
            'skills' => $skills
        ]);
    }

    #[Route('/account-company', name: 'account_company')]
    public function account_company(): Response
    {

        // si je suis un candidat -> redirection
        if ($this->getUser()->getUserType() == 'candidate') {
            return $this->redirectToRoute('account_candidate');
        }

        $em = $this->registryManager->getManager();
        $company = $em->getRepository(Company::class)->findBy(['user' => $this->getUser()]); 
        $job = $em->getRepository(Job::class)->findBy(['published_by' => $this->getUser()]); 
        
        

        return $this->render('company/account.html.twig', [
            'company' => $company[0],
            'job' => $job,
        ]);
    }

   
}
