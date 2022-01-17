<?php

namespace App\Controller;

use App\Entity\Report;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{

    private $registryManager;

    public function __construct(ManagerRegistry $registryManager){
        $this->registryManager = $registryManager;
    }


    #[Route('/report', name: 'report')]
    #[IsGranted("ROLE_USER")]
    public function report(): Response
    {        
        $errors = [];

        if(!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            if(strlen($safe['title']) < 1 || strlen($safe['title']) > 255) {
                $errors[] = 'Veuillez entrer un titre comportant au maximum 255 caractères';
            }

            if(strlen($safe['description'] < 1)) {
                $errors[] = 'Veuillez entrer une description';
            }       
            
            if (count($errors) == 0) {

                $em = $this->registryManager->getManager();

                $report = new Report();

                $report
                    ->setTitle($safe['title'])
                    ->setDescription($safe['description'])
                    ->setUser($this->getUser());

                $em->persist($report);
                $em->flush();

                $this->addFlash('success', 'Votre ticket a bien été envoyé au support, merci de participer à la bonne santé du site.');

                return $this->redirectToRoute('home');
            } 
            else {
                $this->addFlash('danger', implode('<br>', $errors));
            }

        }

        return $this->render('report/index.html.twig');

    }
}
