<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\Job;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminController extends AbstractController
{


    private $registryManager;

    public function __construct(ManagerRegistry $registryManager)
    {
        $this->registryManager = $registryManager;
    }



    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {

        
        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $company = $em->getRepository(Company::class)->findAll();
        
        return $this->render('admin/index.html.twig', [
            'companies' => $company,
        ]);
    }

    #[Route('/admin/list/companies', name: 'admin_companies')]
    public function list_companies(): Response
    {

        
        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $company = $em->getRepository(Company::class)->findAll();

        return $this->render('admin/list_companies.html.twig', [
            'companies' => $company,
        ]);
    }
    #[Route('/admin/list/jobs', name: 'admin_jobs')]
    public function list_jobs(): Response
    {

        
        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $jobs = $em->getRepository(Job::class)->findAll();

        return $this->render('admin/list_jobs.html.twig', [
            'jobs' => $jobs,
        ]);
    }
}
