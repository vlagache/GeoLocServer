<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlertController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * @Route("/alert/number/{id}")
     * @param $id User
     * @return Response
     */
    public function numberOfAlerts($id) :Response
    {
        $data = array();
        $user = $this->userRepository->find($id);
        $alerts = $user->getAlerts();
        $nbOfAlerts = count($alerts);
        $data['nbOfAlerts'] = $nbOfAlerts;
        return new JsonResponse($data);
    }

    /**
     * @Route("/alert/display/{id}")
     * @param $id User
     * @return Response
     */
    public function displayAlerts($id) :Response
    {
        $datas = array();
        $user = $this->userRepository->find($id);
        $alerts = $user->getAlerts();
        foreach ($alerts as $alert)
        {
            $datas[$alert->getActivity()->getUser()->getName()][] = $alert;
        }
        return $this->render('alerts.html.twig',[
           'alerts' => $datas
        ]);
    }
}