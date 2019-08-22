<?php


namespace App\Controller;


use App\Entity\Position;
use App\Entity\User;
use App\Service\CheckActivity;
use App\Service\SendNotification;
use DateTime;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PositionController extends AbstractController
{

    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var UserRepository
     */
    private $repository;
    /**
     * @var CheckActivity
     */
    private $checkActivity;
    /**
     * @var SendNotification
     */
    private $notification;

    public function __construct(UserRepository $repository, ObjectManager $em, CheckActivity $checkActivity,
                                SendNotification $notification)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->checkActivity = $checkActivity;
        $this->notification = $notification;
    }

    /**
     * @Route("/position/{id}", name="positions.send")
     * @param $id User
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function sendPositions($id,Request $request) :Response
    {
        $notification = $this->notification;
        $data = array();
        $lat = $request->request->get('latitude');
        $lng = $request->request->get('longitude');


        $user = $this->repository->find($id);
        if($user)
        {
            $activity = $user->getActivity();
            $checkActivity = $this->checkActivity;
            if($activity)
            {
                date_default_timezone_set('Europe/Paris');
                $position = new Position();
                $position->setActivity($activity)
                    ->setTime(new DateTime())
                    ->setLat($lat)
                    ->setLng($lng);
                $this->em->persist($position);
                $this->em->flush();

                $checkActivity->setActivity($activity); // Activité à check
                if(!$checkActivity->isValid())
                {
                    $notification->setUser($user);
                    $notification->setMessage('immobile');
                    $checkActivity->createAlert();
                    $notification->sendNotification();
                    $data['result'] = 'Errors';
                } else {
                    $data['result'] = 'PositionSave';
                }
            }
        }
        return new JsonResponse($data);
    }
}