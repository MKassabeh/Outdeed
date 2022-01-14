<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\Job;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/company')]
class CompanyController extends AbstractController
{
    private $registryManager;

    private $sort = ['base', 'nameASC', 'nameDESC', 'cityASC', 'cityDESC'];

    public function __construct(ManagerRegistry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    /**
     * Liste des entreprises 
     */
    #[Route('/list', name: 'company_list')]
    public function list(): Response
    {
        $repository = $this->registryManager->getManager()->getRepository(Company::class);

        $companies = $repository->findAll();


        if (!empty($_GET['sort'])) {

            if (in_array($_GET['sort'], $this->sort)) {

                switch ($_GET['sort']) {
                    case '0':
                        $companies = $repository->findAll();                        
                        break;                    
                    case 'nameASC':
                        $companies = $repository->findBy([], ['name'=>'ASC']);
                        break;
                    case 'nameDESC':
                        $companies = $repository->findBy([], ['name'=>'DESC']);
                        break;
                    case 'cityASC':
                        $companies = $repository->findBy([], ['city'=>'ASC']);
                        break;
                    case 'cityDESC':
                        $companies = $repository->findBy([], ['city'=>'DESC']);
                        break;
                }
            }  
        }               

        return $this->render('company/list.html.twig', [
            'companies' => $companies,
        ]);
    }

    // Ajouter entreprise
    #[IsGranted('ROLE_USER')]
    #[Route('/fill', name: 'company_fill')]
    public function add(): Response
    {

        // si je suis ni une entreprise, ni un administrateur 
        if ($this->getUser()->getUserType() !== 'company' && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            //-> je ne peux pas créé d'entreprise
            $this->addFlash('warning', 'En tant que chercheur d\'emploi, vous n\'êtes pas autorisé à créer une entreprise.');
            return $this->redirectToRoute('home');
        }

        if ($this->getUser()->getCompleted()) {
            //Problème de redirection à revoir 
            return $this->redirectToRoute('account_company');
        }

        $controller = new JobController($this->registryManager);
        $categories = $controller->categories;

        $errors = [];

        if (!empty($_POST)) {
            $safe = array_map('trim', array_map('strip_tags', $_POST));

            // Vérif titre
            if (strlen($safe['name']) < 5 || strlen($safe['name']) > 100) {
                $errors[] = 'Votre titre doit comporter entre 5 et 100 caractères';
            }
            // Vérif catégorie
            if (!isset($safe['category'])) {
                $errors[] = 'Veuillez sélectionner une catégorie';
            } elseif (!in_array($safe['category'], $categories)) {
                $errors[] = 'Votre catégorie sélectionnée n\'existe pas';
            }

            // Vérif description entreprise
            if (strlen($safe['description']) < 1 || strlen($safe['description']) > 2000) {
                $errors[] = 'La description de votre entreprise doit comporter entre 1 et 2000 caractères';
            }
            // Vérif city
            if (strlen($safe['city']) < 1 || strlen($safe['city']) > 100) {
                $errors[] = 'Le nom de votre ville doit comporter entre 1 et 100 caractères';
            }
            // Vérif rue
            if (strlen($safe['street']) < 5 || strlen($safe['street']) > 300) {
                $errors[] = 'Veuillez entrer une rue valide';
            }
            // Vérif code postal
            if (!is_numeric($safe['postal_code']) || strlen($safe['postal_code']) != 5) {
                $errors[] = 'Veuillez entrer un code postal valide';
            }
            // Vérif employees
            if (!is_numeric($safe['nb_employees']) || ($safe['nb_employees']) < 1) {
                $errors[] = 'Veillez entrer le nombre d\'employés de votre entreprise';
            }

            //Verif numéro de téléphone
            $pattern = "#^[0][6-7][0-9]{8}$#";

            if (!preg_match($pattern, $safe['phone'])) {
                $errors[] = 'Veuillez entrer un numéro de téléphone portable correct (format : 0601020304)';
            }

            //vérif email
            if (!filter_var($safe['contact_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'email invalide';
            }

            //vérif date 
            if (!checkdate($safe['birth_m'], $safe['birth_d'], $safe['birth_y'])) {
                $errors[] = 'Veuillez renseigner une date de création correcte';
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


            if (count($errors) === 0) {                

                $em = $this->registryManager->getManager();
                $company = new Company();

                // concatenation de l'adresse dans la variable $address
                $address = $safe['street'].' '.$safe['postal_code'].' '.$safe['city'];

                // Clé d'api
                $key_opencage = "b122ef2a93f1403197607658f1e122ac"; 
                // Adresse pour la quelle je cherche les coordonnées GPS

                // Décode l'adresse pour obtenir la latitude / longitude
                $geocoder = new \OpenCage\Geocoder\Geocoder($key_opencage);
                $data = $geocoder->geocode($address);
                $latitude = $data['results'][0]['geometry']['lat'];
                $longitude = $data['results'][0]['geometry']['lng'];

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

                $company->setName($safe['name']);
                $company->setCategory($safe['category']);
                $company->setDescription($safe['description']);
                $company->setStreet($safe['street']);
                $company->setPostalCode($safe['postal_code']);
                $company->setCity($safe['city']);
                $company->setPdp($finalFileNamePDP);
                $company->setPhone($safe['phone']);
                $company->setNbEmployees($safe['nb_employees']);
                $company->setContactEmail($safe['contact_email']);
                $company->setCreatedAt(new \DateTime($safe['birth_d'] . '-' . $safe['birth_m'] . '-' . $safe['birth_y']));
                $company->setUser($this->getUser());
                $company->setLng($longitude);
                $company->setLat($latitude);
                
                $em->persist($company);
                $em->flush();

                $user = $em->getRepository(User::class)->find($this->getUser());
                $user->setCompleted(true);
                $em->persist($user);
                $em->flush();

                //Envoi du message flash
                $this->addFlash('success', 'Votre fiche entreprise a bien été enregistrée');
                return $this->redirectToRoute('account_company');
            } else { // Ici j'ai des erreurs et j'affiche celles-ci
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }
        return $this->render('company/add.html.twig', [
            'categories' => $categories,
        ]);
    }

    // Suppression entreprise
    #[Route('/delete/{id}', name: 'company_delete')]
    public function delete(int $id): Response
    {
        $em = $this->registryManager->getManager();
        $company = $em->getRepository(Company::class)->find($id);

        if (isset($_POST['submit'])) {

            $em->remove($company);
            $em->flush();

            //Envoi d'un message flash de supréssion d'une entreprise
            $this->addFlash('success', 'Votre entreprise a bien été supprimé');
            if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
                return $this->redirectToRoute('admin');
            }
            if(in_array('ROLE_USER', $this->getUser()->getRoles())){
                return $this->redirectToRoute('company_list');
            }
            //Redirection vers la liste des entreprises après la supression d'un élément 
            
        }

        return $this->render('company/delete.html.twig', [
            'company' => $company,
        ]);
    }

    // Modification entreprise
    #[Route('/edit/{id}', name: 'company_edit')]
    public function edit(int $id): Response
    {

        // on récupère le tableau des catégories qui sont stockés dans le JobController
        $jobController = new JobController($this->registryManager);
        $categories = $jobController->categories;

        $em = $this->registryManager->getManager();
        $company = $em->getRepository(Company::class)->find($id);

        $errors = [];

        if (!empty($_POST)) {
            $safe = array_map('trim', array_map('strip_tags', $_POST));

            // Vérif titre
            if (strlen($safe['name']) < 5 || strlen($safe['name']) > 100) {
                $errors[] = 'Votre titre doit comporter entre 5 et 100 caractères';
            }
            // Vérif description
            if (strlen($safe['description']) < 5 || strlen($safe['description']) > 3000) {
                $errors[] = 'Votre description doit comporter entre 5 et 3000 caractères';
            }

            // Vérif catégorie
            if (!isset($safe['category'])) {
                $errors[] = 'Veuillez sélectionner une catégorie';
            } elseif (!in_array($safe['category'], $categories)) {
                $errors[] = 'Votre catégorie sélectionnée n\'existe pas';
            }
            // Vérif contact mail
            if (!filter_var($safe['contact_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Votre Email n\'est pas valide';
            }

            if (!checkdate($safe['birth_m'], $safe['birth_d'], $safe['birth_y'])) {
                $errors[] = 'Veuillez renseigner une date de naissance correcte';
            }
            // Vérif city
            if (strlen($safe['city']) < 5 || strlen($safe['city']) > 100) {
                $errors[] = 'Veuillez entrer une ville valide';
            }
            // Vérif phone
            if (!is_numeric($safe['phone']) || strlen($safe['phone']) != 10) {
                $errors[] = 'Veuillez entrer un numéro de téléphone valide';
            }
            // Vérif nb_employees
            if (!is_numeric($safe['nb_employees']) || strlen($safe['nb_employees']) < 1 || strlen($safe['nb_employees']) > 10) {
                $errors[] = 'Votre salaire doit comporter entre 1 et 100 caractères';
            }



            if (count($errors) == 0) {
                // On assigne les nouvelles valeurs
                $company->setName($safe['name'])
                    ->setDescription($safe['description'])
                    ->setCategory($safe['category'])
                    ->setContactEmail($safe['contact_email'])
                    ->setCity($safe['city'])
                    ->setPhone($safe['phone'])
                    ->setnbEmployees($safe['nb_employees'])
                    ->setCreatedAt(new \DateTime($safe['birth_d'] . '-' . $safe['birth_m'] . '-' . $safe['birth_y']));

                $em->persist($company);
                $em->flush();

                $this->addFlash('success', 'Votre offre d\'emploi a bien été modifiée');
                return $this->redirectToRoute('company_list');
            } else {
                $this->addFlash('danger', implode('<br>', $errors));
            }
        }
        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'categories_availables' => $categories,
        ]);
    }

    // Vue entreprise
    #[Route('/view/{id}', name: 'company_view')]
    public function view(int $id): Response
    {
        //To  access the category and logo tables within the Jobcontroller
        $controller = new JobController($this->registryManager);

        $categories = $controller->categories;
        $logoCategories = $controller ->logoCategories;


        $em = $this->registryManager->getManager();

        //By creating a $company variable and using the Entity manager 
        // we can access the Company class by using the getRepository() function and the find($id) function
        $company = $em->getRepository(Company::class)->find($id);

        //In order to recover the companies belonging to the same category as the one currently on the view,
        //we create the $companyCategory variable and we set it equal to the $company varibale previously created
        //By doing so we are now able to use the getCategory() function on the entity
        $companyCategory = $company ->getCategory();
 
        //Now we create the $companies variable and use the $em in order to access the Company class, getRepository(Company::class)
        //this way we are able to find the desired table by using ->findBy(['category'=>$companyCategory]); 
        $companies = $em->getRepository(Company::class)->findBy(['category'=> $companyCategory]);

        $key_google = 'AIzaSyCiV2wpadEQxdTVMW9h5kuZsL3_uHyOsik';

      
        return $this->render('company/view.html.twig', [
            'company'                  =>     $company,
            'companies'                =>     $companies,
            'categories'               =>     $categories,
            'logoCategories'           =>     $logoCategories,
            'key_google'               =>     $key_google,
            
        ]);
    }
}
