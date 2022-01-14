<?php

namespace App\Controller;

use App\Entity\Apply;
use App\Entity\Job;
use App\Entity\Company;
use App\Entity\Candidate;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;



class ApplyController extends AbstractController
{
    private $registryManager;

    public function __construct(ManagerRegistry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    #[Route('/apply/{id}', name: 'apply')]
    public function index(
        int $id,
        MailerInterface $mailer
    ): Response {

        $em = $this->registryManager->getManager();
        $job = $em->getRepository(Job::class)->find($id);

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            // Vérif titre
            if (strlen($safe['motivation']) <= 0 || strlen($safe['motivation']) > 3000) {
                $errors[] = 'Le message de motivation est obligatoire et doit comporter un maximum de 3000 caractères';
            }


            if (count($errors) === 0) {
                // ici, je n'ai pas d'erreur, j'enregistre en base de données

                $em = $this->registryManager->getManager(); // Connexion à la bdd (équivalent new PDO()) sans oublier le paramètre de la fonction ManagerRegistry $doctrine et le "use"


                
                $job_apply = new Apply();

                $job_apply->setMotivationLetter($safe['motivation']);

                $job_apply->setUser($this->getUser());
                $job_apply->setJob($job);


                // Equivalent à notre execute()
                $em->persist($job_apply);
                $em->flush(); // On libère la base de données (elle arrete d'être en attente de quelque chose);

                // Envoi du mail
                $company_name = $job->getCompanyName();
                $company_email = $em->getRepository(Company::class)->findOneBy(['name' => $company_name])->getContactEmail();
                $email = (new Email())
                    ->from('notifications@outdeed.com')
                    ->to($company_email)
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject('Offre de candidature!')
                    ->text('un candidat à postuler à cette offre!')
                    ->html('<p>Un candidat vient de postuler !</p>');

                $mailer->send($email);



                // Envoi d'un message flash
                $this->addFlash('success', 'Bravo, votre candidature a bien été envoyée');
            } else { // Ici j'ai des erreurs et j'affiche celle-ci
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }
        return $this->render('apply/index.html.twig', [
            'job' => $job,
        ]);
    }

    #[Route('/account/applicants', name: 'offer_applicant')]
    #[IsGranted('ROLE_USER')]
    public function applicants(): Response
    {

        // Si l'user est ni une company ni un admin -> redirection
        if ($this->getUser()->getUserType() != 'company' && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash('warning', 'Cette page est reservée aux entreprises publiant des offres d\'emplois');
            return $this->redirectToRoute('job_list');
        }

        $em = $this->registryManager->getManager();

        // On récupère les offres de l'entreprise
        $offers = $em->getRepository(Job::class)->findBy(['published_by' => $this->getUser()]);

        $postulations = [];


        // On boucle sur les offres pour récupérer les candidatures
        foreach ($offers as $offer) {
            $postulations[] = [
                'job'        => $offer,
                'applicants' => $em->getRepository(Apply::class)->findBy(['job' => $offer->getId()]),
            ];
        }

        // on cherche les fiches candidat associées
        $list = [];
        foreach ($postulations as $post) {
            foreach ($post['applicants'] as $app) {
                $post['candidates'][] = $em->getRepository(Candidate::class)->findOneBy(['user' => $app->getUser()]);
            }
            $list[] = $post;
        }        

        // foreach ($applicants[0] as $app) {
        //     $candidate[] = $em->getRepository(Candidate::class)->findBy(['user' => $app->getUser()]);            
        // }     

        // dd($applicants, $candidate);

        // PROBLEME :

        // Je récupère les offres postés par l'entreprise connectée (stockées dans $offers) [Ligne 90]
        // Pour chaque offre de $offers, je stocke dans le tableau applicants[] les lignes de Apply (la/les postulations) 
        // -> donc $applicants[0] contient toutes les postulations pour l'offre $offers[0] [Ligne 92/94]
        // Ensuite pour chaque élément de l'array $applicants[]
        // je stock dans un array $candidate[] la fiche candidat associé a lapostulation [Ligne 96/98]

        // problème : c'est le bordel, je me perds, j'ai deux tableaux dégeulasses contenant eux-même des tableaux
        // au secours (voir le dd en dessous)


        return $this->render('apply/view.html.twig', [
            'postulations' => $list,

        ]);
    }
}
