<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ReverseGeocoding;
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

    /**
     * @var ReverseGeocoding
     */
    private $reverseGeocoding;

    public function __construct(UserRepository $userRepository, ReverseGeocoding $reverseGeocoding)
    {
        $this->userRepository = $userRepository;
        $this->reverseGeocoding = $reverseGeocoding;
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
        $reverseGeocoding = $this->reverseGeocoding;
        $user = $this->userRepository->find($id);
        $alerts = $user->getAlerts();


        foreach ($alerts as $alert)
        {
            $reverseGeocoding->setAlert($alert);
            $location = $reverseGeocoding->reverseGeocoding();

            $array_alert = array('datetime' => $alert->getDate()->format('d/m/Y'. ' à ' .'H:i:s'), 'location' =>
                $location, 'latitude' => $alert->getLat() , 'longitude' => $alert->getLng());
            $datas[$alert->getActivity()->getUser()->getName()][] = $array_alert;
        }
        return new JsonResponse($datas);
//        return $this->render('base.html.twig');
    }
}