<?php


namespace App\Controller;


use App\Entity\Activity;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Kreait\Firebase;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;

class ActivityController extends AbstractController
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(ObjectManager $em, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    /**
     * @Route("/activity/start/{id}" , name="start.activity")
     * @param $id User id
     * @return Response
     * @throws \Exception
     */
    public function  startActivity($id) :Response
    {
        $data = array();
        $activityStart = false;
        $user = $this->userRepository->find($id);
        if($user) // l'activité est lancé par un utilisateur qui existe.
        {
            $teams = $user->getTeams()->toArray();
            if(count($teams) == 0 )
            {
                $data['result'] = 'userHaveNoTeam';
            } else
            {
                for($i = 0 ; $i<count($teams); $i++)
                {
                    $users = $teams[$i]->getUser()->toArray(); // Tableau d'objet User equipe $i , $i+1 , etc...
                    if( count($users) > 1 )
                    {
                        $activityStart = true; // L'utilisateur a un ami ou plus dans une de ses équipes.
                    }
                }

                $activityExist = $user->getActivity();
                if(!$activityExist && $activityStart) {
                    date_default_timezone_set('Europe/Paris');

                    $activity = new Activity();
                    $activity->setUser($user)
                        ->setDate(new DateTime())
                        ->setTime(new DateTime());
                    $this->em->persist($activity);
                    $this->em->flush();
                    $data['result'] = 'startActivity';
                } else if(!$activityStart){
                    $data['result'] = 'noFriendInYourTeam';
                } else {
                    $data['result'] = 'activityExist';
                }
            }
        }
        return new JsonResponse($data);
//        return $this->render('base.html.twig');
    }

    /**
     * @Route("/activity/delete/{id}" , name="delete.activity")
     * @param $id User Id
     * @return Response
     */
    public function  deleteActivity($id) :Response
    {
        $data = array();
        $user = $this->userRepository->find($id);
        if($user)
        {
            $activity = $user->getActivity();

            if($activity)
            {
                $this->em->remove($activity);
                $this->em->flush();
                $data['result'] = 'deleteActivity';
            } else
            {
                 $data['result'] = 'activityDoesntExist';
            }
        }
        return new JsonResponse($data);
//        return $this->render('base.html.twig');
    }

    /**
     * @Route("/activity/pause/{id}")
     * @param $id User id
     * @return Response
     */
    public function verifyIfYouHaveAnActivity($id) :Response
    {
        $data = array();
        $user = $this->userRepository->find($id);
        if($user)
        {
            $activity = $user->getActivity();
            if(!$activity)
            {
                $data['result'] = 'activityDoesntExist';
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/notification")
     */
    public function sendNotification() : Response
    {

        $firebase = (new Firebase\Factory())->create();
        $messaging = $firebase->getMessaging();


        $configFirebase = parse_ini_file('../config.ini');
        $deviceToken = $configFirebase['deviceToken'];

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create('Notification from Symfony', 'Test'));

        $messaging->send($message);
        return new Response("Notification");
    }
}