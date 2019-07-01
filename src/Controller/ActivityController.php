<?php


namespace App\Controller;


use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Repository\UserRepository;
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
     * @var ActivityRepository
     */
    private $activityRepository;
    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(ObjectManager $em, UserRepository $userRepository, ActivityRepository
    $activityRepository)
    {
        $this->userRepository = $userRepository;
        $this->activityRepository = $activityRepository;
        $this->em = $em;
    }

    /**
     * @Route("/activity/start/{id}" , name="start.activity")
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function  startActivity($id) :Response
    {
        $data = array();
        $user = $this->userRepository->find($id);

        if($user) // l'activité correspond a un utilisateur existant
        {
            $activityExist = $this->activityRepository->findActivityByUserId($id);
            if(!$activityExist)
            {
                date_default_timezone_set('Europe/Paris');

                $activity = new Activity();
                $activity->setUser($user)
                    ->setDate(new DateTime())
                    ->setTime(new DateTime());
                $this->em->persist($activity);
                $this->em->flush();
                $data['result'] = 'startActivity';
            } else {
                $data['result'] = 'activityExist';
            }
        }
        return new JsonResponse($data);
    }
}