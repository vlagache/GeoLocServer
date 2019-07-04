<?php


namespace App\Controller;


use App\Entity\Position;
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

    public function __construct(UserRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/position/{id}", name="positions.send")
     * @param $id
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
            }
        }
//        return new JsonResponse($data);
        return new JsonResponse($data);

//        return $this->render('base.html.twig');

    }
}