<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/company')]
class CompanyController extends AbstractController
{
    private $registryManager;

    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;
    }
    

    
    // liste entreprises
    #[Route('/list', name: 'company_list')]
    public function list(): Response
    {
        return $this->render('company/list.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }



    // Ajouter entreprise
    #[Route('/add', name: 'company_add')]
    public function add(): Response
    {
        return $this->render('company/add.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }



    // Suppression entreprise
    #[Route('/delete/{id}', name: 'company_delete')]
    public function delete(int $id): Response
    {
        $em = $this->registryManager->getManager();
        $company = $em->getRepository(Company::class)->find($id);

        if(isset($_POST['submit'])){
            
            $em->remove($company);
            $em->flush();

            //Envoi d'un message flash de supréssion d'une entreprise
            $this->addFlash('succes', 'Company deleted');

            //Redirection vers la liste d'offres d'emploi après la supression d'un élément 
            return $this->redirectToRoute('company_list');
        }

        return $this->render('company/delete.html.twig', [
            'company' => $company,
        ]);
    }


    // Modifier entreprise
    #[Route('/edit', name: 'company_edit')]
    public function edit(): Response
    {
        return $this->render('company/edit.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }



     // Vue entreprise
    #[Route('/view/{id}', name: 'company_view')]
    public function view(int $id): Response
    {
        $em = $this->registryManager->getManager();
        $company = $em->getRepository(Company::class)->find($id); 
        
        return $this->render('company/view.html.twig', [
            'company' => $company
        ]);

    }
}
