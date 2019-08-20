<?php


namespace App\Controller;


use App\Repository\NotificationRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var NotificationRepository
     */
    private $notificationRepository;

    public function __construct(ObjectManager $em, NotificationRepository $notificationRepository)
    {
        $this->em = $em;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @Route("/cron/deletenotifications")
     * @return Response
     */
    public function deleteNotificationsWithNullActivity() :Response
    {
        $data = array();
        $notifications = $this->notificationRepository->findNotificationsWithNullActivity();
//        if($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
//        {
            foreach($notifications as $notification)
            {
                $this->em->remove($notification);
                $this->em->flush();
                $data['message'] = 'Notifications without activity deleted';
            }
//        } else {
//            $data['message'] = 'Authentication Required';
//        }

        return new JsonResponse($data);
    }
}