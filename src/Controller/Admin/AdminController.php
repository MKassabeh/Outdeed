<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\Job;
use App\Entity\Report;
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
        $jobs = $em->getRepository(Job::class)->findAll();
        $reports = $em->getRepository(Report::class)->findAll();
        
        return $this->render('admin/index.html.twig', [
            'companies' => $company,
            'jobs' => $jobs,
            'reports' => $reports,
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

    #[Route('/admin/list/reports', name: 'admin_reports')]
    public function list_reports(): Response
    {

        
        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $reports = $em->getRepository(Report::class)->findAll();

        return $this->render('admin/list_reports.html.twig', [
            'reports' => $reports,
        ]);
    }

    
    #[Route('/admin/list/report/{id}', name: 'admin_report_view')]
    public function view_reports(int $id): Response
    {


        $em = $this->registryManager->getManager();

        $report = $em->getRepository(Report::class)->find($id);
        return $this->render('admin/report_view.html.twig', [
            'report' => $report,
        ]);
    }
    #[Route('/admin/list/reported/{id}', name: 'admin_reported')]
    public function reported(int $id): Response
    {


        $em = $this->registryManager->getManager();

        $reported = $em->getRepository(Report::class)->find($id);
        $reports = $em->getRepository(Report::class)->findAll();
        
        $reported->setAdminChecked(true);

        $em->persist($reported);
        
        $em->flush();
       

        return $this->render('admin/list_reports.html.twig', [
            'reports' => $reports,
        ]);
    }
}

