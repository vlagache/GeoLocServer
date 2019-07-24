<?php


namespace App\Controller;


use App\Entity\Position;
use App\Service\CheckActivity;
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

    public function __construct(UserRepository $repository, ObjectManager $em, CheckActivity $checkActivity)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->checkActivity = $checkActivity;
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
        $data = array();
        $lat = $request->request->get('latitude');
        $lng = $request->request->get('longitude');


        $user = $this->repository->find($id);
        if($user) // La position est est envoyé par un utilisateur qui existe.
        {
            $activity = $user->getActivity();
//            $checkActivity = $this->checkActivity;
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
                $data['result'] = 'PositionSave';

//                $checkActivity->setActivity($activity);
//                if($checkActivity->isValid()) {
//                    $data['result'] = 'PositionSave';
//                } else {
//                    $checkActivity->sendNotification();
//                    $data['result'] = 'Errors';
//                }
            }
        }
        return new JsonResponse($data);
//        return $this->render('base.html.twig');

    }
}