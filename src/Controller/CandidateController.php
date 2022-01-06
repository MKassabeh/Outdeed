<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/account')]
class CandidateController extends AbstractController
{

    private ManagerRegistry $registryManager;

    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;        
    }

    #[Route('/candidate', name: 'candidate_view')]
    public function view(): Response
    {
        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $candidate = $em->getRepository(Candidate::class)->findBy(['user' => $this->getUser()]); 
        
        return $this->render('candidate/view.html.twig', [
            'candidate' => $candidate
        ]);
    }

    /* #[Route('/candidate/edit', name: 'candidate_edit')]
    public function edit(): Response
    {      

        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $candidate = $em->getRepository(Candidate::class)->findBy(['user' => $this->userId]); 
        
        return $this->render('candidate/view.html.twig', [
            'candidate' => $candidate
        ]);
    } */

    #[Route('/candidate/fill', name: 'candidate_fill')]
    public function fill(): Response
    {

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            $pattern = '/^(?:(?:\+|00)33|0)\s*[1-9](?:[-]*\d{2}){4}$/';

            if (!preg_match($pattern, $safe['phone'])) {
                $errors[] = 'Veuillez entrer un numéro de téléphone correct (06-01-02-03-04 ou +336-01-02-03-04)';
            }
            
            if (strlen($safe['city']) > 255 || strlen($safe['city']) < 0) {
                $errors[] = 'Veuillez entrer un nom ville d\'au maximum 255 caractères';
            } 

            if (strlen($safe['first_name']) > 50 || strlen($safe['first_name']) < 0) {
                $errors[] = 'Votre prénom ne peut pas dépasser 50 caractères';
            }  
                     
            if (strlen($safe['last_name']) > 50 || strlen($safe['last_name']) < 0) {
                $errors[] = 'Votre nom de famille ne peut pas dépasser 50 caractères';
            }           



        }

        return $this->render('candidate/fill.html.twig', [
           
        ]);
    }


}
