<?php


namespace App\Service;


use App\Entity\User;
use Kreait\Firebase;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;

class SendNotification
{
    private $message;
    public function __construct()
    {
        $this->message = '';
    }
    /**
     * @var User
     */
    private $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * return tokens firebase of friends user
     * @return array
     */
    public function getTokens()
    {
        $tokens = array();
        $id = $this->user->getId();
        $teams = $this->user->getTeams();
        foreach($teams as $team)
        {
            $friends = $team->getUser()->toArray(); // Array d'objets User
            for($i = 0 ; $i<count($friends); $i++)
            {
                if($friends[$i]->getId() == $id)
                {
                    unset($friends[$i]);
                    $friends = array_values($friends);
                }
            }
            foreach($friends as $friend)
            {
                $token = $friend->getDevice()->getTokenFirebase();
                array_push($tokens , $token);
            }
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
        return $success;
    }
    public function setMessageActivityStart()
    {
        $this->message = CloudMessage::new()
            ->withNotification(Notification::create('Notification GeoLocApp', 'Votre ami '. $this->user->getName() .' à démarré une activité !'));
    }

    public function setMessageActivityPause()
    {
        $this->message = CloudMessage::new()
            ->withNotification(Notification::create('Notification GeoLocApp', 'Votre ami '. $this->user->getName() .' à mis en pause son activité !'));
    }
    public function setMessageActivityRestart()
    {
        $this->message = CloudMessage::new()
            ->withNotification(Notification::create('Notification GeoLocApp', 'Votre ami '. $this->user->getName() .' à relancé son activité !'));
    }

    public function setMessageActivityEnd()
    {
        $this->message = CloudMessage::new()
            ->withNotification(Notification::create('Notification GeoLocApp', 'Votre ami '. $this->user->getName() .' à arreté son activité !'));
    }
}