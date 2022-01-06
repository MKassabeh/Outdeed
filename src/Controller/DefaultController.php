<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        if ($this->getUser() !== null) {             

            if (!$this->getUser()->getCompleted()) {
                return $this->redirectToRoute('candidate_fill');
            }
        }
        return $this->render('default/home.html.twig', []);
    }
}
