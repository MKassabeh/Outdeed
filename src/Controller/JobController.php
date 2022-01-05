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

    public $contractType = [
        'CDI - Temps plein',
        'CDI - Temps partiel',
        'CDD - Temps plein',
        'CDD - Temps partiel',
        'Interim',
        'Apprentissage',
        'Stage',
        'Contrat professionnel',
        'CDI de chantier ou d\'opérations'
    ];

    public $categories = [
        'Agriculture',
        'Alimentation',
        'Animaux',
        'Architecture',
        'Services publics',
        'Banque',
        'Biologie',
        'BTP',
        'Cinema',
        'Immobilier',
        'Communication',
        'Culture',
        'Droit',
        'Electricité',
        'Hôtellerie-Restauration',
        'Informatique',
        'Mécanique',
        'Santé',
        'Sport',
        'Secretariat',
        'Transport-Logistique'
    ]; 

    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;
    }

    // liste des jobs
    #[Route('/', name: 'job_list')]
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
        $em = $this->registryManager->getManager();
        $job = $em->getRepository(Job::class)->find($id);

        return $this->render('job/view.html.twig', [
            'job'        => $job,
            'categories' => $this->categories,
        ]);
    }

    // Ajout job
    #[Route('/add', name: 'job_add')]
    public function add(): Response
    {
        $errors = [];
        $formIsValid = null;

        if(!empty($_POST)){
            $safe = array_map('trim', array_map('strip_tags', $_POST));

                // Vérif titre
                if(strlen($safe['title']) < 5 || strlen($safe['title']) > 100){
                    $errors[] = 'Votre titre doit comporter entre 5 et 100 caractères';
                }

                // Vérif catégorie
                if(!isset($safe['category'])){
                    $errors[] = 'Veuillez sélectionner une catégorie';
                }
                elseif(!in_array($safe['category'], $this->categories)){
                    $errors[] = 'Votre catégorie sélectionnée n\'existe pas';
                }

                // Vérif description entreprise
                if(strlen($safe['description_company']) < 1){
                    $errors[] = 'Veuillez entrer la description de l\'entreprise';
                }
                // Vérif description emploi
                if(strlen($safe['description_job']) < 1){
                    $errors[] = 'Veuillez entrer la description de l\'emploi';
                }
                // Vérif description recherche
                if(strlen($safe['description_applicant']) < 1){
                    $errors[] = 'Veuillez entrer la description du profil recherché';
                }
                // Vérif city
                if(strlen($safe['city']) < 1 || strlen($safe['city']) > 100){
                    $errors[] = 'Votre salaire doit comporter entre 1 et 100 caractères';
                }

                // Vérif salaires
                if(strlen($safe['wages']) < 1 || strlen($safe['wages']) > 50){
                    $errors[] = 'Votre salaire doit comporter entre 1 et 50 caractères';
                }
                
                // Vérif type contrat
                if(!isset($safe['contract'])){
                    $errors[] = 'Veuillez sélectionner un type de contrat';
                }
                elseif(!in_array($safe['contract'], $this->contractType)){
                    $errors[] = 'Votre type de contrat n\'existe pas';
                }
                // Vérif horaires
                if(strlen($safe['schedule']) > 255){
                    $errors[] = 'Vos indications concernant les horaires ne doivent pas dépasser 255 caractères.';
                }
                // Vérif commentaire
                if(strlen($safe['comment']) > 500){
                    $errors[] = 'Votre commentaire doit comporter moins de 500 caractères';
                }

                if(count($errors) === 0){
                    // ici, je n'ai pas d'erreur, j'enregistre en base de données
    
                    $em = $this->registryManager->getManager(); // Connexion à la bdd (équivalent new PDO()) sans oublier le paramètre de la fonction ManagerRegistry $doctrine et le "use"
    
    
    
                    // Equivalent de notre INSERT INTO et bindValue()
                    $job = new Job(); // Appelle de l'entity job 
                    // On enregistre chaque valeur
                    $job->setTitle($safe['title']);
                    $job->setCategory($safe['category']);
                    $job->setDescriptionCompany($safe['description_company']);
                    $job->setDescriptionApplicant($safe['description_applicant']);
                    $job->setDescriptionJob($safe['description_job']);
                    $job->setWages($safe['wages']);
                    $job->setCity($safe['city']);
                    $job->setContractType($safe['contract']);
                    $job->setPublishedAt(new \DateTime('now'));
                    $job->setSchedule($safe['schedule']);
                    $job->setCompanyComment($safe['comment']);
    
                    // Equivalent à notre execute()
                    $em->persist($job);
                    $em->flush(); // On libère la base de données (elle arrete d'être en attente de quelque chose);
    
                    // Envoi d'un message flash
                    $this->addFlash('success','Bravo, votre offre d\'emploi a bien été enregistrée');  
    
                }
                else { // Ici j'ai des erreurs et j'affiche celle-ci
                    $this->addFlash('danger', implode('<br>', $errors));
                }
    
            }         
            return $this->render('job/add.html.twig', [
                'categories_availables' => $this->categories,
                'contract_type'         => $this->contractType
    
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
