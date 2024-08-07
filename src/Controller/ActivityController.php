<?php


namespace App\Controller;


use App\Entity\Activity;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SendNotification;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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
    /**
     * @var SendNotification
     */
    private $notification;

    public function __construct(ObjectManager $em, UserRepository $userRepository, SendNotification $notification)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->notification = $notification;
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
        $notification = $this->notification;
        $activityCanStart = false;
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
                        $activityCanStart = true; // L'utilisateur a un ami ou plus dans une de ses équipes.
                    }
                }

                $activityExist = $user->getActivity(); // Activité deja lancé
                if(!$activityExist && $activityCanStart) {
                    date_default_timezone_set('Europe/Paris');

                    $activity = new Activity();
                    $activity->setUser($user)
                        ->setDate(new DateTime())
                        ->setTime(new DateTime())
                        ->setPause(false);
                    $this->em->persist($activity);
                    $this->em->flush();
                    $data['result'] = 'startActivity';

                    $notification->setUser($user);
                    $notification->setActivity($activity);
                    $notification->setMessage('start');
                    $notification->sendNotification();

                } else if(!$activityCanStart){
                    $data['result'] = 'noFriendInYourTeam';
                } else if ($activityExist->getPause() == 1) {
                    $data['result'] = 'activityRestart';
                    $activityExist->setPause(false);
                    $this->em->flush();

                    $notification->setUser($user);
                    $notification->setMessage('restart');
                    $notification->sendNotification();
                } else {
                    $data['result'] = 'activityExist';
                }
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/activity/delete/{id}" , name="delete.activity")
     * @param $id User Id
     * @return Response
     */
    public function  deleteActivity($id) :Response
    {
        $data = array();
        $notification = $this->notification;
        $user = $this->userRepository->find($id);
        if($user)
        {
            $activity = $user->getActivity();

            if($activity)
            {
                $notification->setUser($user);
                $notification->setActivityNull();
                $notification->setMessage('end');

                $notification->sendNotification();

                $this->em->remove($activity);
                $this->em->flush();
                $data['result'] = 'deleteActivity';
            } else
            {
                 $data['result'] = 'activityDoesntExist';
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/activity/pause/{id}")
     * @param $id User id
     * @return Response
     */
    public function pauseActivity($id) :Response
    {
        $data = array();
        $notification = $this->notification;
        $user = $this->userRepository->find($id);
        if($user)
        {
            $activity = $user->getActivity();
            if(!$activity)
            {
                $data['result'] = 'activityDoesntExist';
            } else
            {
                $data['result'] = "pauseActivity";
                $activity->setPause(true);
                $this->em->flush();

                $notification->setUser($user);
                $notification->setMessage('pause');
                $notification->sendNotification();
            }
        }
        return new JsonResponse($data);
    }
}