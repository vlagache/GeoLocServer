<?php


namespace App\Service;


use App\Entity\Activity;
use App\Entity\User;
use App\Entity\Notification;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Kreait\Firebase;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Messaging\CloudMessage;

class SendNotification
{
    private $em;
    private $message;
    private $body;
    private $activityNull;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        $this->message = '';
        $this->body = '';
        $this->activityNull = false;
    }

    private $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }


    private $activity;

    public function setActivity(Activity $activity)
    {
        $this->activity = $activity;
    }



    public function setActivityNull()
    {
        $this->activityNull = true;
    }

    /**
     * return users to whom a notification is sent
     * @return array
     */
    public function getUsers()
    {
        $users = array();
        $id = $this->user->getId();
        $teams = $this->user->getTeams();
        foreach ($teams as $team) {
            $friends = $team->getUser()->toArray(); // Array d'objets User
            for ($i = 0; $i < count($friends); $i++) {
                if ($friends[$i]->getId() == $id) {
                    unset($friends[$i]);
                    $friends = array_values($friends);
                }
            }
            foreach ($friends as $friend) {
                array_push($users, $friend);
            }
        }
        return $users;
    }

    /**
     * return tokens firebase of friends user
     * @return array
     */
    public function getTokens()
    {
        $tokens = array();
        $friends = $this->getUsers();
        foreach ($friends as $friend) {
            $token = $friend->getDevice()->getTokenFirebase();
            array_push($tokens, $token);
        }

        return $tokens;
    }

    public function sendNotification()
    {
                $firebase = (new Firebase\Factory())->create();
                $messaging = $firebase->getMessaging();
                $deviceTokens = $this->getTokens();

                $messaging->sendMulticast($this->message, $deviceTokens);

                if ( $this->activity == null )
                {
                    $activity = $this->user->getActivity();
                } else {
                    $activity = $this->activity;
                }

                if($this->activityNull == true) $activity = null ;

                $users = $this->getUsers();
                foreach ($users as $user)
                {
                    date_default_timezone_set('Europe/Paris');
                    $notification = new Notification();
                    $notification->setActivity($activity) 
                        ->setUser($user) 
                        ->setDate(new DateTime())
                        ->setMessage($this->body)
                        ->setReadByUser(false);
                        $this->em->persist($notification);
                        $this->em->flush();
                }
    }
    public function setMessage($stateOfActivity)
    {
        switch($stateOfActivity)
        {
            case "start":
                $this->body = 'Votre ami ' . $this->user->getName() . ' a démarré une activité';
                //
                break;
            case "pause":
                $this->body = 'Votre ami ' . $this->user->getName() . ' a mis en pause son activité';
                break;
            case "end":
                $this->body = 'Votre ami ' . $this->user->getName() . ' a arreté son activité';
                break;
            case "restart":
                $this->body = 'Votre ami ' . $this->user->getName() . ' a redémarré son activité';
                break;
            case "immobile":
                $this->body = 'Votre ami ' . $this->user->getName() . ' est immobile';
                break;
            case "move":
                $this->body = 'Votre ami ' . $this->user->getName() . ' bouge';
                break;
        }
        $this->message = CloudMessage::new()
            ->withNotification(FirebaseNotification::create('Notification GeoLocApp', ''.$this->body.''));
        return $this->body;
    }
}