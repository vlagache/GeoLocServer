<?php


namespace App\Controller;


use App\Entity\User;
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

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
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
    public function displayNotifications()
    {
        // Affiche les notifications qui nous sont destinés par activité
    }
}