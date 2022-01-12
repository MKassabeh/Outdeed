<?php

namespace App\Controller;

use App\Entity\Apply;
use App\Entity\Job;
use App\Entity\Company;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class ApplyController extends AbstractController
{
    private $registryManager;


    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;
    }



    #[Route('/apply/{id}', name: 'apply')]
    public function index(int $id): Response
    {

        
        $errors = []; 

        if(!empty($_POST)){
            $safe = array_map('trim', array_map('strip_tags', $_POST));

                // Vérif titre
                if(strlen($safe['motivation_letter']) < 5 || strlen($safe['motivation_letter']) > 100){
                    $errors[] = 'Votre titre doit comporter entre 5 et 100 caractères';
                }                

                if(count($errors) === 0){
                    // ici, je n'ai pas d'erreur, j'enregistre en base de données
    
                    $em = $this->registryManager->getManager(); // Connexion à la bdd (équivalent new PDO()) sans oublier le paramètre de la fonction ManagerRegistry $doctrine et le "use"
    
 
                    $job_apply = new Apply(); 

                    $job_apply->setMotivationLetter($safe['motivation_letter']);

                    $job_apply->setUser($this->getUser());
                    $job_apply->setJob($em->getRepository(Job::class)->find($id));

    
                    // Equivalent à notre execute()
                    $em->persist($job_apply);
                    $em->flush(); // On libère la base de données (elle arrete d'être en attente de quelque chose);
    
                    // Envoi d'un message flash
                    $this->addFlash('success','Bravo, votre offre d\'emploi a bien été enregistrée');  
                }
                else { // Ici j'ai des erreurs et j'affiche celle-ci
                    $this->addFlash('danger', implode('<br>', $errors));
                }
            } 
        return $this->render('apply/index.html.twig', [
            'controller_name' => 'ApplyController',
        ]);
    }
}
