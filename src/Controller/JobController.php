<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job')]
class JobController extends AbstractController
{

    private $registryManager;

    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;
    }

    // liste des jobs
    #[Route('/', name: 'job_list')]
    public function list(): Response
    {
        return $this->render('job/list.html.twig', [
            'controller_name' => 'JobController',
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
        $em = $this->registryManager->getManager();
        $job = $em->getRepository(Job::class)->find($id);

        if(isset($_POST['submit'])){
            
            $em->remove($job);
            $em->flush();

            //Envoi d'un message flash de supréssion d'une offre d'emploi
            $this->addFlash('succes', 'Job offer deleted');

            //Redirection vers la liste d'offres d'emploi après la supression d'un élément 
            return $this->redirectToRoute('job_list');
        }

        return $this->render('job/delete.html.twig', [
            'job' => $job,
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
