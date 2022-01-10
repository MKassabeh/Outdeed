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
    #[IsGranted('ROLE_USER')]
    #[Route('/add', name: 'company_add')]
    public function add(): Response
    {

        // si je suis ni une entreprise, ni un administrateur 
        if ($this->getUser()->getUserType() !== 'company' && !in_array('ROLE_ADMIN',$this->getUser()->getRoles())) {
            //-> je ne peux pas créé d'entreprise
            $this->addFlash('warning', 'En tant que chercheur d\'emploi, vous n\'êtes pas autorisé à créé une entreprise.');
            return $this->redirectToRoute('company_list');
        }

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
            $this->addFlash('success', 'Votre entreprise a bien été supprimé');

            //Redirection vers la liste des entreprises après la supression d'un élément 
            return $this->redirectToRoute('company_list');
        }

        return $this->render('company/delete.html.twig', [
            'company' => $company,
        ]);
    }


    #[Route('/edit/{id}', name: 'company_edit')]
    public function edit(int $id): Response
    {

        // on récupère le tableau des catégories
        $jobController = new JobController($this->registryManager);
        $categories = $jobController->categories;
        
        $em = $this->registryManager->getManager();
        $company = $em->getRepository(Company::class)->find($id);

        $errors = [];        

        if(!empty($_POST)){
            $safe = array_map('trim', array_map('strip_tags', $_POST));

                // Vérif titre
                if(strlen($safe['name']) < 5 || strlen($safe['name']) > 100){
                    $errors[] = 'Votre titre doit comporter entre 5 et 100 caractères';
                }
                // Vérif description
                if(strlen($safe['description']) < 5 || strlen($safe['description']) > 3000){
                    $errors[] = 'Votre description doit comporter entre 5 et 3000 caractères';
                }

                // Vérif catégorie
                if(!isset($safe['category'])){
                    $errors[] = 'Veuillez sélectionner une catégorie';
                }
                elseif(!in_array($safe['category'], $categories)){
                    $errors[] = 'Votre catégorie sélectionnée n\'existe pas';
                }
                // Vérif contact mail
                if(!filter_var($safe['contact_email'],FILTER_VALIDATE_EMAIL)){
                    $errors[] = 'Votre Email n\'est pas valide';
                }


                // Vérif city
                if(strlen($safe['city']) < 5 || strlen($safe['city']) > 100){
                    $errors[] = 'Veuillez entrer une ville valide';
                }
                // Vérif phone
                if(!is_numeric($safe['phone']) || strlen($safe['phone']) != 10){
                    $errors[] = 'Veuillez entrer un numéro de téléphone valide';
                }
                // Vérif nb_employees
                if(!is_numeric($safe['nb_employees']) || strlen( $safe['nb_employees']) < 1 || strlen($safe['nb_employees']) > 10){
                    $errors[] = 'Votre salaire doit comporter entre 1 et 100 caractères';
                }



                if (count($errors) == 0) {
                    // On assigne les nouvelles valeurs
                    $company->setName($safe['name'])
                        ->setDescription($safe['description'])
                        ->setCategory($safe['category'])
                        ->setContactEmail($safe['contact_email'])
                        ->setCity($safe['city'])
                        ->setPhone($safe['phone'])
                        ->setnbEmployees($safe['nb_employees'])
                        ->setCreatedAt(new \DateTime('now'));

                    $em->persist($company);
                    $em->flush();

                    $this->addFlash('success', 'Votre offre d\'emploi a bien été modifiée');
                    return $this->redirectToRoute('company_list');
                        
                } else {
                    $this->addFlash('danger', implode('<br>', $errors));
                }
        }
        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'categories_availables' => $categories,
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
