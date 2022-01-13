<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\ProfessionalExperience;
use App\Entity\Skill;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[IsGranted('ROLE_USER')]
#[Route('/candidate')]
class CandidateController extends AbstractController
{

    private ManagerRegistry $registryManager;

    public function __construct(ManagerRegistry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    /* #[Route('/candidate', name: 'candidate_view')]
    public function view(): Response
    {
        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $candidate = $em->getRepository(Candidate::class)->findBy(['user' => $this->getUser()]);

        return $this->render('candidate/view.html.twig', [
            'candidate' => $candidate
        ]);
    } */

    #[Route('/edit', name: 'candidate_edit')]
    public function edit(): Response
    {
        $em = $this->registryManager->getManager();
        // recupère la fiche candidat de la personne connectée
        $candidate = $em->getRepository(Candidate::class)->findBy(['user' => $this->getUser()]);

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            $pattern = "#^[0][6-7][0-9]{8}$#";

            if (!preg_match($pattern, $safe['phone'])) {
                $errors[] = 'Veuillez entrer un numéro de téléphone portable correct (format : 0601020304)';
            }

            if (strlen($safe['city']) > 255 || strlen($safe['city']) <= 0) {
                $errors[] = 'Veuillez entrer un nom ville d\'au maximum 255 caractères';
            }

            if (strlen($safe['first_name']) > 50 || strlen($safe['first_name']) <= 0) {
                $errors[] = 'Votre prénom ne peut pas dépasser 50 caractères';
            }

            if (strlen($safe['last_name']) > 50 || strlen($safe['last_name']) <= 0) {
                $errors[] = 'Votre nom de famille ne peut pas dépasser 50 caractères';
            }

            if (strlen($safe['address']) > 70 || strlen($safe['address']) <= 0) {
                $errors[] = 'Veuillez renseigner votre adresse, maximum 70 caractères.';
            }

            if (strlen($safe['address']) > 70 || strlen($safe['address']) <= 0) {
                $errors[] = 'Veuillez renseigner votre adresse, maximum 70 caractères.';
            }

            if (!checkdate($safe['birth_m'], $safe['birth_d'], $safe['birth_y'])) {
                $errors[] = 'Veuillez renseigner une date de naissance correcte';
            }

            if (count($errors) == 0) {
                $candidate[0]
                    ->setCity($safe['city'])
                    ->setPhoneNumber($safe['phone'])
                    ->setFirstName($safe['first_name'])
                    ->setLastName($safe['last_name'])
                    ->setUser($this->getUser())
                    ->setAddress($safe['address'])
                    ->setBirthdate(new \DateTime($safe['birth_d'] . '-' . $safe['birth_m'] . '-' . $safe['birth_y']));

                $em->persist($candidate[0]);
                $em->flush();

                $this->addFlash('success', 'Vos informations ont bien été enregistrées.');
                return $this->redirectToRoute('account_candidate');
            } else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }

        return $this->render('candidate/edit.html.twig', [
            'candidate' => $candidate[0]
        ]);
    }

    #[Route('/fill', name: 'candidate_fill')]
    public function fill(SluggerInterface $slugger): Response
    {

        // si je suis ni une entreprise, ni un administrateur 
        if ($this->getUser()->getUserType() !== 'candidate' && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            //-> je ne peux pas créé d'entreprise
            $this->addFlash('warning', 'En tant qu\'entreprise, vous n\'êtes pas autorisé à créer une fiche candidat.');
            return $this->redirectToRoute('home');
        }

        if ($this->getUser()->getCompleted()) {
            return $this->redirectToRoute('account_candidate');
        }

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            $pattern = "#^[0][6-7][0-9]{8}$#";

            if (!preg_match($pattern, $safe['phone'])) {
                $errors[] = 'Veuillez entrer un numéro de téléphone portable correct (format : 0601020304)';
            }

            if (strlen($safe['city']) > 255 || strlen($safe['city']) <= 0) {
                $errors[] = 'Veuillez entrer un nom ville d\'au maximum 255 caractères';
            }

            if (strlen($safe['first_name']) > 50 || strlen($safe['first_name']) <= 0) {
                $errors[] = 'Votre prénom ne peut pas dépasser 50 caractères';
            }

            if (strlen($safe['last_name']) > 50 || strlen($safe['last_name']) <= 0) {
                $errors[] = 'Votre nom de famille ne peut pas dépasser 50 caractères';
            }

            if (strlen($safe['address']) > 70 || strlen($safe['address']) <= 0) {
                $errors[] = 'Veuillez renseigner votre adresse, maximum 70 caractères.';
            }

            if (strlen($safe['address']) > 70 || strlen($safe['address']) <= 0) {
                $errors[] = 'Veuillez renseigner votre adresse, maximum 70 caractères.';
            }

            if (!checkdate($safe['birth_m'], $safe['birth_d'], $safe['birth_y'])) {
                $errors[] = 'Veuillez renseigner une date de naissance correcte';
            }            

            // Je vérifie le fichier uploadé, seulement s'il le champ est présent dans le formulaire            
            if (!empty($_FILES) && isset($_FILES['cv'])) {

                $cv = $_FILES['cv'];
                $fileAllowedMimes = ['application/pdf', 'application/x-pdf']; // Les types mimes attendus -> PDF
                $fileMaxSize = 1024 * 1024 * 10; // Taille maximale de l'image en octet        

                if ($cv['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Le CV est obligatoire';
                } elseif ($cv['error'] === UPLOAD_ERR_OK && !in_array($cv['type'], $fileAllowedMimes)) {
                    $errors[] = 'Le type de fichier n\'est pas autorisé (pdf uniquement)';
                } elseif ($cv['error'] === UPLOAD_ERR_OK && $cv['size'] > $fileMaxSize) {
                    $errors[] = 'Le fichier est trop volumineux, taille maximale autorisée : 10 Mo';
                }
            }

            if (!empty($_FILES) && isset($_FILES['pdp'])) {

                $pdp = $_FILES['pdp'];
                $fileAllowedMimes = ['image/jpg', 'image/jpeg', 'image/png']; // Les types mimes attendus -> Images
                $fileMaxSize = 1024 * 1024 * 10; // Taille maximale de l'image en octet        

                if ($pdp['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'La photo de profil est obligatoire';
                } elseif ($pdp['error'] === UPLOAD_ERR_OK && !in_array($pdp['type'], $fileAllowedMimes)) {
                    $errors[] = 'Le type de fichier n\'est pas autorisé (images uniquement)';
                } elseif ($pdp['error'] === UPLOAD_ERR_OK && $pdp['size'] > $fileMaxSize) {
                    $errors[] = 'Le fichier est trop volumineux, taille maximale autorisée : 10 Mo';
                }
            }


            if (count($errors) == 0) {

                $em = $this->registryManager->getManager();
                $candidate = new Candidate();
                $fileDirUploadCV = 'uploads/CV/'; // Chemin de sauvegarde d'image, à partir de là ou je me trouve

                // CV :                          

                // Permet de récupérer automatiquement l'extension du fichier téléchargé
                $extCV = pathinfo($cv['name'], PATHINFO_EXTENSION);

                // Créer un nom de fichier unique
                $fileNameCV = uniqid() . '.' . $extCV;  // Donnera quelque de similaire à 4b3403665fea6.pdf

                // Sauvegarde mon image
                if (move_uploaded_file($cv['tmp_name'], $fileDirUploadCV . $fileNameCV)) { // $fileDirUpload.$fileName = "../../../public/uploads/CV/4b3403665fea6.jpg"
                    $finalFileNameCV = $fileDirUploadCV . $fileNameCV;
                } else {
                    $finalFileNameCV = null;
                }

                // PDP :

                // Permet de récupérer automatiquement l'extension du fichier téléchargé
                $extPDP = pathinfo($pdp['name'], PATHINFO_EXTENSION);
                $fileDirUploadPDP = 'uploads/PDP/'; // Chemin de sauvegarde d'image, à partir de là ou je me trouve


                // Créer un nom de fichier unique
                $fileNamePDP = uniqid() . '.' . $extPDP;  // Donnera quelque de similaire à 4b3403665fea6.pdf

                // Sauvegarde mon image
                if (move_uploaded_file($pdp['tmp_name'], $fileDirUploadPDP . $fileNamePDP)) { // $fileDirUpload.$fileName = "../../../public/uploads/CV/4b3403665fea6.jpg"
                    $finalFileNamePDP = $fileDirUploadPDP . $fileNamePDP;
                } else {
                    $finalFileNamePDP = null;
                }

                $candidate
                    ->setCity($safe['city'])
                    ->setPhoneNumber($safe['phone'])
                    ->setFirstName($safe['first_name'])
                    ->setLastName($safe['last_name'])
                    ->setUser($this->getUser())
                    ->setCV($finalFileNameCV)
                    ->setPdp($finalFileNamePDP)
                    ->setAddress($safe['address'])
                    ->setBirthdate(new \DateTime($safe['birth_d'] . '-' . $safe['birth_m'] . '-' . $safe['birth_y']));

                $em->persist($candidate);
                $em->flush();

                $user = $em->getRepository(User::class)->find($this->getUser());
                $user->setCompleted(true);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Votre fiche candidat a bien été enregistrée.');
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }

        return $this->render('candidate/fill.html.twig', []);
    }

    #[Route('/experience/add', name: 'experience_add')]
    public function experience_add(): Response
    {

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            if (strlen($safe['job_name']) <= 0 || strlen($safe['job_name']) > 100) {
                $errors[] = 'Le nom du poste doit comprendre entre 1 et 100 caractères';
            }
            if (strlen($safe['company_name']) <= 0 || strlen($safe['company_name']) > 100) {
                $errors[] = 'Le nom de l\'entreprise doit comprendre entre 1 et 100 caractères';
            }
            if (!is_numeric($safe['duration'])) {
                $errors[] = 'Veuillez entrer un nombre d\'années chiffrée.';
            }

            if (count($errors) == 0) {

                $em = $this->registryManager->getManager();
                $exp = new ProfessionalExperience();

                $candidat = $em->getRepository(Candidate::class)->findBy(['user' => $this->getUser()]);

                $exp
                    ->setJobName($safe['job_name'])
                    ->setCompanyName($safe['company_name'])
                    ->setDuration($safe['duration'])
                    ->setCandidate($candidat[0]);

                $em->persist($exp);
                $em->flush();

                $this->addFlash('success', 'Votre expérience professionnelle a bien été ajoutée');
                return $this->redirectToRoute('account_candidate');
            } else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }

        return $this->render('candidate/experience_add.html.twig');
    }

    #[Route('/experience/delete/{id}', name: 'experience_delete')]
    public function experience_delete(int $id): Response
    {

        $em = $this->registryManager->getManager();
        $exp = $em->getRepository(ProfessionalExperience::class)->find($id);

        $errors = [];

        if (!empty($_POST['submit'])) {
            $em->remove($exp);
            $em->flush();

            $this->addFlash('success', 'Votre experience professionnelle a bien été supprimé');
            return $this->redirectToRoute('account_candidate');
        }

        return $this->render('candidate/experience_delete.html.twig', [
            'experience' => $exp
        ]);
    }

    #[Route('/experience/edit/{id}', name: 'experience_edit')]
    public function experience_edit(int $id): Response
    {

        $em = $this->registryManager->getManager();
        $exp = $em->getRepository(ProfessionalExperience::class)->find($id);

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            if (strlen($safe['job_name']) <= 0 || strlen($safe['job_name']) > 100) {
                $errors[] = 'Le nom du poste doit comprendre entre 1 et 100 caractères';
            }
            if (strlen($safe['company_name']) <= 0 || strlen($safe['company_name']) > 100) {
                $errors[] = 'Le nom de l\'entreprise doit comprendre entre 1 et 100 caractères';
            }
            if (!is_numeric($safe['duration'])) {
                $errors[] = 'Veuillez entrer un nombre d\'années chiffrée.';
            }

            if (count($errors) == 0) {

                $exp
                    ->setJobName($safe['job_name'])
                    ->setCompanyName($safe['company_name'])
                    ->setDuration($safe['duration']);

                $em->persist($exp);
                $em->flush();

                $this->addFlash('success', 'Votre expérience professionnelle a bien été modifiée');
                return $this->redirectToRoute('account_candidate');
            } else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }

        return $this->render('candidate/experience_edit.html.twig', [
            'experience' => $exp
        ]);
    }

    #[Route('/skill/add', name: 'skill_add')]
    public function skill_add(): Response
    {

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            if (strlen($safe['skill']) <= 0 || strlen($safe['skill']) > 100) {
                $errors[] = 'Le nom du poste doit comprendre entre 1 et 100 caractères';
            }

            if (!is_numeric($safe['duration'])) {
                $errors[] = 'Veuillez entrer un nombre d\'années chiffrée.';
            }

            if (count($errors) == 0) {

                $em = $this->registryManager->getManager();
                $skill = new Skill();

                $candidat = $em->getRepository(Candidate::class)->findBy(['user' => $this->getUser()]);

                $skill
                    ->setSkillName($safe['skill'])
                    ->setYearsOfExperience($safe['duration'])
                    ->setCandidate($candidat[0]);

                $em->persist($skill);
                $em->flush();

                $this->addFlash('success', 'Votre compétence technique a bien été ajoutée');
                return $this->redirectToRoute('account_candidate');
            } else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }

        return $this->render('candidate/skill_add.html.twig');
    }

    #[Route('/skill/edit/{id}', name: 'skill_edit')]
    public function skill_edit(int $id): Response
    {

        $em = $this->registryManager->getManager();
        $skill = $em->getRepository(Skill::class)->find($id);

        $errors = [];

        if (!empty($_POST)) {

            $safe = array_map('trim', array_map('strip_tags', $_POST));

            if (strlen($safe['skill']) <= 0 || strlen($safe['skill']) > 100) {
                $errors[] = 'Le nom du poste doit comprendre entre 1 et 100 caractères';
            }

            if (!is_numeric($safe['duration'])) {
                $errors[] = 'Veuillez entrer un nombre d\'années chiffrée.';
            }

            if (count($errors) == 0) {

                $skill
                    ->setSkillName($safe['skill'])
                    ->setYearsOfExperience($safe['duration']);

                $em->persist($skill);
                $em->flush();

                $this->addFlash('success', 'Votre compétence technique a bien été modifiée');
                return $this->redirectToRoute('account_candidate');
            } else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }

        return $this->render('candidate/skill_edit.html.twig', [
            'skill' => $skill
        ]);
    }

    #[Route('/skill/delete/{id}', name: 'skill_delete')]
    public function skill_delete(int $id): Response
    {

        $em = $this->registryManager->getManager();
        $skill = $em->getRepository(Skill::class)->find($id);

        if (!empty($_POST['submit'])) {

            $em->remove($skill);
            $em->flush();

            $this->addFlash('success', 'Votre compétence technique a bien été supprimé');
            return $this->redirectToRoute('account_candidate');
        }

        return $this->render('candidate/skill_delete.html.twig', [
            'skill' => $skill
        ]);
    }
    
}
