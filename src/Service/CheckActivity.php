<?php


namespace App\Service;


use App\Entity\Activity;
use App\Entity\Alert;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;



class CheckActivity
{
    /**
     * @var Activity
     */
    private $activity;
    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var SendNotification
     */
    private $notification;
    /**
     * @var DistanceBetweenTwoPoints
     */
    private $distance;

    public function __construct(ObjectManager $em, SendNotification $notification,DistanceBetweenTwoPoints $distance)
    {
        $this->em = $em;
        $this->notification = $notification;
        $this->distance = $distance;
    }

    public function setActivity(Activity $activity)
    {
        $this->activity = $activity;
    }
    /**
     * check if user move
     * @return bool|null
     */
    public function checkIfMove()
    {
        $positions = $this->activity->getPositions();
        if(count($positions) < 2) return null; // Start pas de positions à comparer
//        if($positions[1]->getLat() == $positions[0]->getLat() && $positions[1]->getLng() == $positions[0]->getLng())return false;
        //
        $distance = $this->distance;
        $distance->setPosition1($positions[0]);
        $distance->setPosition2($positions[1]);
        $distanceBetween = $distance->distanceBetweenTwoPoints();
        if ($distanceBetween <= 5 ) return false;

        return true; // Mouvement
    }
    public function isValid()
    {

        if($this->activity->getPause() == 1 )
        {
            $valid = 1;
        } else if($this->checkIfMove() == false)
        {
            $valid = 0;
        } else { // checkIfMove() true or null
            $valid = 1;
        }

        return $valid;

    }
    public function createAlert()
    {
        $notification = $this->notification;
        $users = $notification->getUsers(); // Recuperation de tout les users à qui envoyer l'alert.
        $positions = $this->activity->getPositions();
        $lat = $positions[0]->getLat();
        $lng = $positions[0]->getLng();
        foreach($users as $user)
        {
            date_default_timezone_set('Europe/Paris');
            $alert = new Alert();
            $alert->setActivity($this->activity)
                ->setUser($user)
                ->setDate(new DateTime())
                ->setLat($lat)
                ->setLng($lng);
                $this->em->persist($alert);
                $this->em->flush();
        }


    }


}