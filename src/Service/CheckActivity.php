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
    /**
     * @var ReverseGeocoding
     */
    private $reverseGeocoding;

    public function __construct(ObjectManager $em, SendNotification $notification,DistanceBetweenTwoPoints $distance,
                                ReverseGeocoding $reverseGeocoding)
    {
        $this->em = $em;
        $this->notification = $notification;
        $this->distance = $distance;
        $this->reverseGeocoding = $reverseGeocoding;
    }

    public function setActivity(Activity $activity)
    {
        $this->activity = $activity;
    }
    /**
     * check if user move
     * @return bool
     */
    public function checkIfMove()
    {
        $positions = $this->activity->getPositions();
        if(count($positions) < 2) return true;// Start pas de positions à comparer

        $distance = $this->distance;
        $distance->setPosition1($positions[0]);
        $distance->setPosition2($positions[1]);
        $distanceBetween = $distance->distanceBetweenTwoPoints();

        if ($distanceBetween <= 20 ) return false;

        return true; // Mouvement
    }
    public function isValid()
    {
        if($this->activity->getPause() == 1 ) return true;
        if(!$this->checkIfMove()) return false;
        return true;

    }
    public function createAlert()
    {
        $notification = $this->notification;
        $reverseGeocoding = $this->reverseGeocoding;

        $users = $notification->getUsers(); // Recuperation de tout les users à qui envoyer l'alert.
        $positions = $this->activity->getPositions();
        $lat = $positions[0]->getLat();
        $lng = $positions[0]->getLng();

        $reverseGeocoding->setPosition($positions[0]);
        $address = $reverseGeocoding->reverseGeocoding();

        foreach($users as $user)
        {
            date_default_timezone_set('Europe/Paris');
            $alert = new Alert();
            $alert->setActivity($this->activity)
                ->setUser($user)
                ->setDate(new DateTime())
                ->setLat($lat)
                ->setLng($lng)
                ->setAddress($address);
                $this->em->persist($alert);
                $this->em->flush();
        }
    }
}