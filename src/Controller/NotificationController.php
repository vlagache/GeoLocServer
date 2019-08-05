<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ActivityRepository
     */
    private $activityRepository;

    public function __construct(UserRepository $userRepository, ActivityRepository $activityRepository)
    {
        $this->userRepository = $userRepository;
        $this->activityRepository = $activityRepository;
    }
    /**
     * @Route("/notification/number/{id}")
     * @param $id User
     * @return Response
     */
    public function numberOfNotifications($id) :Response
    {
        $data = array();
        $user = $this->userRepository->find($id);
        $notifications = $user->getNotifications();
        $nbOfNotifications = count($notifications);

        $data['nbOfNotifications'] = $nbOfNotifications;

        return new JsonResponse($data);
//        return $this->render('base.html.twig');
    }

    /**
     * @Route("/notification/display/{id}")
     * @param $id User
     * @return Response
     */
    public function displayNotifications($id) : Response
    {
        $arrayId = array();
        $user = $this->userRepository->find($id);
        $notifications = $user->getNotifications();
        foreach ($notifications as $notification)
        {
            $activityId = $notification->getActivity()->getId();
            array_push($arrayId, $activityId);
            $result = array_unique($arrayId);
        }

        $activities = array();
        for($i=0; $i<count($result); $i++)
        {
            $activity = $this->activityRepository->find($result[$i]);
            array_push($activities , $activity);
        }

        return $this->render('notifications.html.twig', [
           'activities' => $activities,
            'userIdWhoAskDisplay' => $id
        ]);

//        return $this->render('base.html.twig');
    }
}