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

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        $this->message = '';
        $this->body = '';
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

                $report = $messaging->sendMulticast($this->message, $deviceTokens);

                $success = 'Successful sends: '.$report->successes()->count().PHP_EOL;


                if ( $this->activity == null )
                {
                    $activity = $this->user->getActivity();
                } else {
                    $activity = $this->activity;
                }

                $users = $this->getUsers();
                foreach ($users as $user)
                {
                    date_default_timezone_set('Europe/Paris');
                    $notification = new Notification();
                    $notification->setActivity($activity) // Activité  a laquelle est lié la notification
                        ->setUser($user) // User a qui on a envoyé la notification
                        ->setDate(new DateTime())
                        ->setMessage($this->body); // contenu du message.
                        $this->em->persist($notification);
                        $this->em->flush();
                }

                return $success;

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

//    public function setMessageActivityStart()
//    {
//        $this->message = CloudMessage::new()
//            ->withNotification(FirebaseNotification::create('Notification GeoLocApp', 'Votre ami ' . $this->user->getName()
//                . ' a démarré une activité !'));
//    }
//
//    public function setMessageActivityPause()
//    {
//        $this->message = CloudMessage::new()
//            ->withNotification(FirebaseNotification::create('Notification GeoLocApp', 'Votre ami ' . $this->user->getName() . ' a mis en pause son activité !'));
//    }
//
//    public function setMessageActivityRestart()
//    {
//        $this->message = CloudMessage::new()
//            ->withNotification(FirebaseNotification::create('Notification GeoLocApp', 'Votre ami ' . $this->user->getName() . ' a relancé son activité !'));
//    }
//
//    public function setMessageActivityEnd()
//    {
//        $this->message = CloudMessage::new()
//            ->withNotification(FirebaseNotification::create('Notification GeoLocApp', 'Votre ami ' . $this->user->getName() . ' a arreté son activité !'));
//    }
//
//    public function setMessageActivityAlert()
//    {
//        $this->message = CloudMessage::new()
//            ->withNotification(FirebaseNotification::create('Notification GeoLocApp', 'Votre ami ' . $this->user->getName() . ' est immobile !'));
//    }
//
//    public function setMessageActivityMove()
//    {
//        $this->message = CloudMessage::new()
//            ->withNotification(FirebaseNotification::create('Notification GeoLocApp', 'Votre ami ' . $this->user->getName() . ' bouge !'));
//    }
}