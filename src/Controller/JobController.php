<?php

namespace App\Controller;

use App\Entity\Job;
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
        $categories_availables = [
            'property'  => 'Immobilier',
            'clothes'   => 'Vêtements',
            'cars'      => 'Voitures',
            'holidays'  => 'Vacances',
        ];
        $errors = [];
        $formIsValid = null;

        if(!empty($_POST)){
            $safe = array_map('trim', array_map('strip_tags', $_POST));

                // Vérif titre
                if(strlen($safe['title']) < 5 || strlen($safe['title']) > 50){
                    $errors[] = 'Votre titre doit comporter entre 5 et 50 caractères';
                }

                // Vérif catégorie
                if(!isset($safe['category'])){
                    $errors[] = 'Veuillez sélectionner une catégorie';
                }
                elseif(!in_array($safe['category'], array_keys($categories_availables))){
                    $errors[] = 'Votre catégorie sélectionnée n\'existe pas';
                }

                // Vérif description
                if(strlen($safe['description_company']) < 10 || strlen($safe['description_company']) > 500){
                    $errors[] = 'Votre description de company doit comporter entre 10 et 500 caractères';
                }
                // Vérif description
                if(strlen($safe['description_job']) < 10 || strlen($safe['description_job']) > 500){
                    $errors[] = 'Votre description du job doit comporter entre 10 et 500 caractères';
                }
                // Vérif description
                if(strlen($safe['description_applicant']) < 10 || strlen($safe['description_applicant']) > 500){
                    $errors[] = 'Votre description de profil doit comporter entre 10 et 500 caractères';
                }
                // Vérif description
                if(strlen($safe['city']) < 10 || strlen($safe['city']) > 500){
                    $errors[] = 'Votre salaire doit comporter entre 10 et 500 caractères';
                }
                if(strlen($safe['wages']) < 10 || strlen($safe['wages']) > 500){
                    $errors[] = 'Votre salaire doit comporter entre 10 et 500 caractères';
                }
                // Vérif description
                if(strlen($safe['contract']) < 10 || strlen($safe['contract']) > 500){
                    $errors[] = 'Votre contrat doit comporter entre 10 et 500 caractères';
                }
                // Vérif description
                if(strlen($safe['schedule']) < 10 || strlen($safe['schedule']) > 500){
                    $errors[] = 'Votre horaire doit comporter entre 10 et 500 caractères';
                }
                // Vérif description
                if(strlen($safe['comment']) < 10 || strlen($safe['comment']) > 500){
                    $errors[] = 'Votre commentaire doit comporter entre 10 et 500 caractères';
                }

                if(count($errors) === 0){
                    // ici, je n'ai pas d'erreur, j'enregistre en base de données
    
                    $em = $this->registryManager->getManager(); // Connexion à la bdd (équivalent new PDO()) sans oublier le paramètre de la fonction ManagerRegistry $doctrine et le "use"
    
    
    
                    // Equivalent de notre INSERT INTO et bindValue()
                    $annonce = new Job(); // Appelle de l'entity Annonces 
                    // On enregistre chaque valeur
                    $annonce->setTitle($safe['title']);
                    $annonce->setCategory($safe['category']);
                    $annonce->setDescriptionCompany($safe['description_company']);
                    $annonce->setDescriptionApplicant($safe['description_applicant']);
                    $annonce->setDescriptionJob($safe['description_job']);
                    $annonce->setWages($safe['wages']);
                    $annonce->setCity($safe['city']);
                    $annonce->setContractType($safe['contract']);
                    $annonce->setPublishedAt(new \DateTime('now'));
                    $annonce->setSchedule($safe['schedule']);
                    $annonce->setCompanyComment($safe['comment']);
    
    
    
    
                     // Equivalent à notre execute()
                    $em->persist($annonce);
                    $em->flush(); // On libère la base de données (elle arrete d'être en attente de quelque chose);
    
                    // Envoi d'un message flash
                    $this->addFlash('success','Bravo, votre annonce a bien été enregistrée');
    
    
                }
                else { // Ici j'ai des erreurs et j'affiche celle-ci
                    $this->addFlash('danger', implode('<br>', $errors));
                }
    
            } 


        
            return $this->render('job/add.html.twig', [
                'categories_availables' => $categories_availables,
    
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
