<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/account')]
class CandidateController extends AbstractController
{

    private ManagerRegistry $registryManager;

    public function __construct(ManagerRegistry $registryManager) {
        $this->registryManager = $registryManager;        
    }
   
    #[Route('/', name: 'account')]
    public function account(): Response {

        if (!$this->getUser()->getCompleted()) {
            $this->addFlash('info', 'Veuillez entrer vos données personnelles');
            return $this->redirectToRoute('candidate_fill');
        }

        $em = $this->registryManager->getManager();
        $candidate = $em->getRepository(Candidate::class)->findBy(['user' => $this->getUser()]);       
       

        return $this->render('candidate/account.html.twig', [
            'candidate' => $candidate[0]
        ]);
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

        if ($this->getUser()->getCompleted()) {
            return $this->redirectToRoute('home');
        }
        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            $pattern = "#^[0][6-7][0-9]{8}$#";

            if (!preg_match($pattern, $safe['phone'])) {
                $errors[] = 'Veuillez entrer un numéro de téléphone portable correct (format : 0601020304)';
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
            
            if (count($errors) == 0) {
                
                $em = $this->registryManager->getManager();
                $candidate = new Candidate();

                $candidate
                    ->setCity($safe['city'])
                    ->setPhoneNumber($safe['phone'])
                    ->setFirstName($safe['first_name'])
                    ->setLastName($safe['last_name'])
                    ->setUser($this->getUser());

                $em->persist($candidate);
                $em->flush();

                $user = $em->getRepository(User::class)->find($this->getUser());
                $user->setCompleted(true);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Vos informations ont bien été enregistrées.');
                return $this->redirectToRoute('home');
            }
            else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }

        return $this->render('candidate/fill.html.twig', [
           
        ]);
    }


}
