<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Apply;
use Doctrine\Persistence\ManagerRegistry;

class NotificationCounter
{

    private $registryManager;
    

    public function __construct(ManagerRegistry $registryManager)
    {
        $this->registryManager = $registryManager;
    
    }    

    public function getNotification(int $user): int {        
       
        $notifications= [];
        $postulants = $this->registryManager->getManager()->getRepository(Apply::class)->findAll();

        // Pour chaque "postulation"
        foreach ($postulants as $post) {
            // si elle concerne une de mes offres je l'ajoute à notifications[]
            if($post->getJob()->getPublishedBy()->getId() == $user){
                $notifications[] = $post;
            }
        }        

        $how_many = count($notifications);

        return $how_many;
    }
}
