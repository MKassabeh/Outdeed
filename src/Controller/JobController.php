<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Job;

#[Route('/job')]
class JobController extends AbstractController
{

    private $registryManager;

    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;
    }

    // liste des jobs
    #[Route('/list', name: 'job_list')]
    public function list(): Response
    {
        $repository = $this->registryManager->getManager()->getRepository(Job::class);
        $jobs = $repository->findAll();
        return $this->render('job/list.html.twig', [
            'jobs' => $jobs,
        ]);
    }

    // Vue d'un job
    #[Route('/view/{id}', name: 'job_view')]
    public function view(int $id): Response
    {
        return $this->render('job/view.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }

    // Ajout job
    #[Route('/add', name: 'job_add')]
    public function add(): Response
    {
        return $this->render('job/add.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }

    // Suppression job
    #[Route('/delete/{id}', name: 'job_delete')]
    public function delete(int $id): Response
    {
        return $this->render('job/delete.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }

    // Edition job
    #[Route('/edit/{id}', name: 'job_edit')]
    public function edit(int $id): Response
    {
        return $this->render('job/edit.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }
}
