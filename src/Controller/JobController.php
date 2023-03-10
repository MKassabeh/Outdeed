<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\Company;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


#[Route('/job')]
class JobController extends AbstractController
{
    private $registryManager;

    private $sort = ['base', 'nameASC', 'nameDESC', 'dateASC', 'dateDESC','categoryASC', 'categoryDESC'];

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

    public $logoCategories = [
        'Agriculture' => 'agriculture.png',
        'Alimentation' => 'alimentation.png',
        'Animaux' => 'animaux.png',
        'Architecture' => 'architecte.png',
        'Services publics' => 'servicepublic.png',
        'Banque' => 'banque.png',
        'Biologie' => 'biologie.png',
        'BTP' => 'batiment.png',
        'Cinema' => 'cinema.png',
        'Immobilier' => 'immobilier.png',
        'Communication' => 'communication.png',
        'Culture' => 'culture.png',
        'Droit' => 'droit.png',
        'Electricité' => 'electricien.png',
        'Hôtellerie-Restauration' => 'restaurant.png',
        'Informatique' => 'informatique.png',
        'Mécanique' => 'mecanique.png',
        'Santé' => 'sante.png',
        'Sport' => 'sport.png',
        'Secretariat' => 'secretariat.png',
        'Transport-Logistique' => 'transportlog.png'
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
        
        // BARRE DE RECHERCHE        
        
        if (!empty($_GET['search']))
        {
            $jobs = $repository->search($_GET['search']);
        }

        // Algo de tri

        // TRI + CATEGORIE FIXE
        if (!empty($_GET['sort']) && !empty($_GET['category']) && in_array($_GET['category'], $this->categories)) {            

            switch ($_GET['sort']) {
                case '0':
                    $jobs = $repository->findAll();                        
                    break;                    
                case 'nameASC':
                    $jobs = $repository->findBy(['category' => $_GET['category']], ['title'=>'ASC']);
                    break;
                case 'nameDESC':
                    $jobs = $repository->findBy(['category' => $_GET['category']], ['title'=>'DESC']);
                    break;
                case 'dateASC':
                    $jobs = $repository->findBy(['category' => $_GET['category']], ['published_at'=>'ASC']);
                    break;
                case 'dateDESC':
                    $jobs = $repository->findBy(['category' => $_GET['category']], ['published_at'=>'DESC']);
                    break;
                case 'categoryASC':
                    $jobs = $repository->findBy(['category' => $_GET['category']], ['category'=>'ASC']);
                    break;
                case 'categoryDESC':
                    $jobs = $repository->findBy(['category' => $_GET['category']], ['category'=>'DESC']);
                    break;
            }

        // UNIQUEMENT CATEGORIE FIXE
        } else if(empty($_GET['sort']) && !empty($_GET['category']) && in_array($_GET['category'], $this->categories)){
            if(!empty($_GET['category']) && in_array($_GET['category'], $this->categories)) {
                $jobs = $repository->findBy(['category' => $_GET['category']]);
            }

        // UNIQUEMENT LE TRI
        } else if (!empty($_GET['sort']) && empty($_GET['category'])) {

            switch ($_GET['sort']) {
                case '0':
                    $jobs = $repository->findAll();                        
                    break;                    
                case 'nameASC':
                    $jobs = $repository->findBy([], ['title'=>'ASC']);
                    break;
                case 'nameDESC':
                    $jobs = $repository->findBy([], ['title'=>'DESC']);
                    break;
                case 'dateASC':
                    $jobs = $repository->findBy([], ['published_at'=>'ASC']);
                    break;
                case 'dateDESC':
                    $jobs = $repository->findBy([], ['published_at'=>'DESC']);
                    break;
                case 'categoryASC':
                    $jobs = $repository->findBy([], ['category'=>'ASC']);
                    break;
                case 'categoryDESC':
                    $jobs = $repository->findBy([], ['category'=>'DESC']);
                    break;
            }

        }
        
        // tri par catégorie

        // Si le formulaire est soumis
        // findBy-> where catégory = $_GET['category'];
        
        
        return $this->render('job/list.html.twig', [
            'jobs' => $jobs,
            'logoCategories' => $this->logoCategories,
            'categories' => $this->categories           
        ]);
        
    }

    // Vue d'un job
    #[Route('/view/{id}', name: 'job_view')]
    public function view(int $id): Response
    {
        $em = $this->registryManager->getManager();
        $job = $em->getRepository(Job::class)->find($id);
        $jobs = $em->getRepository(Job::class)->findBy(['category' => $job->getCategory()]);

        // Suggestions d'annonce
        
    
        return $this->render('job/view.html.twig', [
            'job'        => $job,
            'categories' => $this->categories,
            'logoCategories' => $this->logoCategories,
            'jobs'       => $jobs
        ]);
    }

    // Ajout job
    #[IsGranted('ROLE_USER')]
    #[Route('/add', name: 'job_add')]
    public function add(): Response
    {

        // si je suis ni une entreprise, ni un administrateur 
        if ($this->getUser()->getUserType() !== 'company' && !in_array('ROLE_ADMIN',$this->getUser()->getRoles())) {
            //-> je ne peux pas ajouter d'offre d'emploi
            $this->addFlash('warning', 'En tant que chercheur d\'emploi, vous n\'êtes pas autorisé à publier des offres d\'emploi.');
            return $this->redirectToRoute('job_list');
        }


        $errors = [];       

        if(!empty($_POST)){
            $safe = array_map('trim', array_map('strip_tags', $_POST));

                // Vérif titre
                if(strlen($safe['title']) < 5 || strlen($safe['title']) > 100){
                    $errors[] = 'Votre titre doit comporter entre 5 et 100 caractères';
                }                
                
                // Vérif description emploi
                if(strlen($safe['description_job']) < 1){
                    $errors[] = 'Veuillez entrer la description de l\'emploi';
                }
                // Vérif description recherche
                if(strlen($safe['description_applicant']) < 1){
                    $errors[] = 'Veuillez entrer la description du profil recherché';
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

                if(strlen($safe['comment']) > 2500){
                    $errors[] = 'Votre commentaire doit comporter moins de 2500 caractères';
                }

                if(count($errors) === 0){
                    // ici, je n'ai pas d'erreur, j'enregistre en base de données
    
                    $em = $this->registryManager->getManager(); // Connexion à la bdd (équivalent new PDO()) sans oublier le paramètre de la fonction ManagerRegistry $doctrine et le "use"
    
                    $company = $em->getRepository(Company::class)->findBy(['user' => $this->getUser()]);

                    // Equivalent de notre INSERT INTO et bindValue()
                    $job = new Job(); // Appelle de l'entity job 
                    // On enregistre chaque valeur
                    $job->setTitle($safe['title']);
                    $job->setCategory($company[0]->getCategory());
                    $job->setDescriptionCompany($company[0]->getDescription());
                    $job->setCompanyName($company[0]->getName());
                    $job->setDescriptionApplicant($safe['description_applicant']);
                    $job->setDescriptionJob($safe['description_job']);
                    $job->setWages($safe['wages']);
                    $job->setCity($company[0]->getCity());
                    $job->setContractType($safe['contract']);
                    $job->setPublishedAt(new \DateTime('now'));
                    $job->setSchedule($safe['schedule']);
                    $job->setPublishedBy($this->getUser());
                    $job->setCompanyComment($safe['comment']);
                    $job->setBenefits($safe['benefit']);

    
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
        // on récupère l'élément à modifier
        $em = $this->registryManager->getManager();
        $job = $em->getRepository(Job::class)->find($id);

        $errors = [];        

        if(!empty($_POST)){
            $safe = array_map('trim', array_map('strip_tags', $_POST));

                // Vérif titre
                if(strlen($safe['title']) < 5 || strlen($safe['title']) > 100){
                    $errors[] = 'Votre titre doit comporter entre 5 et 100 caractères';
                }                
                
                // Vérif description emploi
                if(strlen($safe['description_job']) < 1){
                    $errors[] = 'Veuillez entrer la description de l\'emploi';
                }
                // Vérif description recherche
                if(strlen($safe['description_applicant']) < 1){
                    $errors[] = 'Veuillez entrer la description du profil recherché';
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

                if (count($errors) == 0) {
                    // On assigne les nouvelles valeurs
                    $job->setTitle($safe['title'])
                        ->setCategory($safe['category'])
                        ->setDescriptionCompany($safe['description_company'])
                        ->setDescriptionApplicant($safe['description_applicant'])
                        ->setDescriptionJob($safe['description_job'])
                        ->setWages($safe['wages'])
                        ->setCity($safe['city'])
                        ->setContractType($safe['contract'])
                        ->setPublishedAt(new \DateTime('now'))
                        ->setSchedule($safe['schedule'])
                        ->setCompanyComment($safe['comment']);

                    $em->persist($job);
                    $em->flush();

                    $this->addFlash('success', 'Votre offre d\'emploi a bien été modifiée');
                    return $this->redirectToRoute('job_list');
                        
                } else {
                    $this->addFlash('danger', implode('<br>', $errors));
                }
        }
        return $this->render('job/edit.html.twig', [
            'job'                   => $job,
            'categories_availables' => $this->categories,
            'contract_type'         => $this->contractType
        ]);
    }      
}
