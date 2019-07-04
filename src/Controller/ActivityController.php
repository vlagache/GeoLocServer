<?php


namespace App\Controller;


use App\Entity\Activity;
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
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function  startActivity($id) :Response
    {
        $data = array();
        $user = $this->userRepository->find($id);
        if($user) // l'activité est lancé par un utilisateur qui existe.
        {
            $activityExist = $user->getActivity();
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
//        return $this->render('base.html.twig');
    }

    /**
     * @Route("/activity/delete/{id}" , name="delete.activity")
     * @param $id
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
}