<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
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

    /**
     * @var NotificationRepository
     */
    private $notificationRepository;
    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(UserRepository $userRepository, ActivityRepository $activityRepository,
                                NotificationRepository $notificationRepository, ObjectManager $em)
    {
        $this->userRepository = $userRepository;
        $this->activityRepository = $activityRepository;
        $this->notificationRepository = $notificationRepository;
        $this->em = $em;
    }
    /**
     * @Route("/notification/number/{id}")
     * @param $id User
     * @return Response
     */
    public function numberOfNotifications($id) :Response
    {

        $data = array();
        $notifications = $this->notificationRepository->findNonReadNotificationsByUser($id);
        $nbOfNonReadNotifications = count($notifications);

        $data['nbOfNonReadNotifications'] = $nbOfNonReadNotifications;

        return new JsonResponse($data);
    }

    /**
     * @Route("/notification/display/{id}")
     * @param $id User
     * @return Response
     */
    public function displayNotifications($id) : Response
    {

        $user = $this->userRepository->find($id);
        $notifications = $user->getNotifications();

        foreach($notifications as $notification)
        {
            $notification->setReadByUser(true);
            $this->em->flush();
            ($notification->getActivity()) ? $activityId = $notification->getActivity()->getId() : $activityId = 0;

            $datas[$activityId][] = $notification;

        }
        return $this->render('notifications.html.twig' , [
            'notifications' => $datas
        ]);
    }
}