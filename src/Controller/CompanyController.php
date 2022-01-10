<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CompanyController extends AbstractController
{
    

    #[Route('/company/list', name: 'company_list')]
    public function list(): Response
    {
        return $this->render('company/list.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }

    #[Route('/company/add', name: 'company_add')]
    public function add(): Response
    {
        return $this->render('company/add.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }

    #[Route('/company/delete', name: 'company_delete')]
    public function delete(): Response
    {
        return $this->render('company/delete.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }

    #[Route('/company/edit', name: 'company_edit')]
    public function edit(): Response
    {
        return $this->render('company/edit.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }

    #[Route('/company/view', name: 'company_view')]
    public function view(): Response
    {
        return $this->render('company/view.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }
}
