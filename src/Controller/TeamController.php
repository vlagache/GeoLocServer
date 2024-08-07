<?php


namespace App\Controller;


use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(TeamRepository $teamRepository, UserRepository $userRepository, ObjectManager $em)
    {
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    /**
     * @Route("/team/create/{id}")
     * @param $id User id
     * @param Request $request
     * @return Response
     */
    public function createTeam($id, Request $request) : Response
    {
        $data = array();
        $nameTeam = $request->request->get('nameTeam');
        $nameTeamFormat = strtoupper(str_replace(' ', '', $nameTeam));
        $user = $this->userRepository->find($id);
        if($user)
        {
                $teams = $user->getTeams()->toArray();

                for($i=0; $i<count($teams); $i++)
                {
                    if($nameTeamFormat == strtoupper(str_replace(' ', '',$teams[$i]->getName())))
                    {
                        $data['result'] = 'AlreadyTeamWithSameName';
                    }
                }

                if(!$data) {
                    $team = new Team();
                    $team->setName($nameTeam);
                    $this->em->persist($team);
                    $team->addUser($user);
                    $this->em->flush();
                    $data['result'] = 'newTeamCreate';
                }
        }
        return new JsonResponse($data);
    }
    /**
     * @Route("/team/{id}/adduser")
     * @param $id Team
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addUserInATeam($id, Request $request) : Response
    {
        $data = array();
        $mail = $request->request->get('mail');
        $idUserWhoAddFriend = $request->request->get('invitFrom');
        $team = $this->teamRepository->find($id);
        $user = $this->userRepository->findUserByMail($mail);
        $userWhoAddFriend = $this->userRepository->find($idUserWhoAddFriend);

        if($team)
        {
            if($user)
            {
                $teams = $user->getTeams()->toArray();
                if(in_array($team, $teams))
                {
                    $data['result'] = 'userAlreadyInTeam';
                } else{
                    $team->addUser($user);
                    $this->em->flush();
                    $data['idUser'] = $user->getId();
                    $data['name'] = $user->getName();
                    $data['idTeam'] = $team->getId();
                    $data['result'] = 'addUser';
                }
            } else
            {
                $data['result'] = 'unknownUser';
                $to = $mail;
                $subject = $userWhoAddFriend->getName(). 'souhaite vous rajouter dans son équipe [ GEOLOCAPP ]';
                $message .= $userWhoAddFriend->getName(). "souhaite vous ajouter dans son équipe sur l'application GEOLOCAPP . Vous pouvez télécharger l'application ici : [ lien ]";
                $headers = 'From: geolocsport@vincentlagache.com' . "\r\n" .
                    'Reply-To: v1.lagache@gmail.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                mail($to, $subject, $message, $headers);
            }
        } else
        {
            $data['result'] = 'unknownTeam';
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/team/{id}/deleteuser")
     * @param $id Team
     * @param Request $request
     * @return Response
     */
    public function removeUserInTeam($id , Request $request)
    {
        $data = array();
        $idUser = $request->request->get('idUser');
        $team = $this->teamRepository->find($id);
        $user = $this->userRepository->find($idUser);

        if($team)
        {
            if($user)
            {
                $teams = $user->getTeams()->toArray();
                if(in_array($team, $teams))
                {
                    $team->removeUser($user);
                    $this->em->flush();
                    $data['result'] = 'removeUser';

                    $nbUsers = $team->getUser()->toArray();
                    if ( count($nbUsers) == 0)
                    {
                        $this->em->remove($team);
                        $this->em->flush();;
                    }
                }
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/team/{id}")
     * @param $id User
     * @return Response
     */
    public function displayTeams($id) : Response
    {
        $user = $this->userRepository->find($id);
        if ($user) {
            $teams = $user->getTeams();
            return $this->render('teamstable.html.twig', [
                'teams' => $teams,
                'userIdWhoAskDisplay' => $id
            ]);
        }
        return $this->render('base.html.twig');
    }
}