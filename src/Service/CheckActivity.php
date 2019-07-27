<?php


namespace App\Service;


use App\Entity\Activity;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class CheckActivity
{
    /**
     * @var Activity
     */
    private $activity;

    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
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
        if(count($positions) < 2) return null; // Demarrage pas de positions à comparer
        if($position[1]->getLatLong() == $position[0]->getLatLong() ) return false; // Immobile
        return true; // Mouvement
    }
    public function isValid()
    {
        // if pause
        return null;

        $valid = 0;
        // prendre toutes les positions
        if($this->checkIfMove() == 1 or $this->checkIfMove() == null ) $valid = 1;

        // verfier si c bon par position

        return $valid;
    }

    public function sendNotification()
    {
        return 'note';
    }

    public function createAlert()
    {
        return 'note';
    }


}