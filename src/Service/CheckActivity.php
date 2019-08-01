<?php


namespace App\Service;


use App\Entity\Activity;
use Doctrine\Common\Persistence\ObjectManager;


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
        if(count($positions) < 2) return null; // Start pas de positions à comparer
        if($positions[1]->getLat() == $positions[0]->getLat() && $positions[1]->getLng() == $positions[0]->getLng())return false;
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

    public function sendNotification()
    {
        return 'note';
    }

    public function createAlert()
    {
        return 'note';
    }


}