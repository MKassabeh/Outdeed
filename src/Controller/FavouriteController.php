<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Job;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/favourite')]
#[IsGranted('ROLE_USER')]
class FavouriteController extends AbstractController
{

    private $registryManager;

    public function __construct(ManagerRegistry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    #[Route('add/{id}', name: 'favourite_add')]
    public function add(int $id): Response
    {
        $em = $this->registryManager->getManager();            

        // l'offre d'emploi en question
        $jobOffer = $em->getRepository(Job::class)->find($id);

        // je check si il est pas déja aux favoris
        if (in_array($jobOffer, $this->getUser()->getFavourite())) {
            $this->addFlash('warning', 'Cette offre est déjà dans vos favoris');
            return $this->redirectToRoute('job_list');
        }

        // je l'ajoute a ses favoris (fonction fournie par sf, cf. User.php)
        $this->getUser()->addFavourite($jobOffer);

        $em->persist($this->getUser());
        $em->flush();
        
        $this->addFlash('info', 'L\'annonce '.$jobOffer->getTitle().', a bien été ajouté a vos favoris');        
        return $this->redirectToRoute('job_list');
    }

    #[Route('/', name: 'favourite_list')]
    public function list(): Response
    {
        $em = $this->registryManager->getManager();
        $favourites = $this->getUser()->getFavourite();
        
        $jobController = new JobController($this->registryManager);
        $logoCategories = $jobController->logoCategories;
              

        return $this->render('favourite/index.html.twig', [
            'favourites' => $favourites,
            'logoCategories' => $logoCategories
        ]);
    }

    #[Route('/delete/{id}', name: 'favourite_delete')]
    public function delete(int $id): Response
    {
        $em = $this->registryManager->getManager();
        $jobOffer = $em->getRepository(Job::class)->find($id);
        $favourites = $this->getUser()->removeFavourite($jobOffer);

        $em->persist($this->getUser());
        $em->flush();
              
        $this->addFlash('info', 'L\'annonce '.$jobOffer->getTitle().' a bien été retirée des favoris');
        return $this->redirectToRoute('favourite_list');
    }


}
