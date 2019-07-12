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
//        $request = Request::create(
//            '/team/create/{id}',
//            'POST',
//            ['nameTeam' => 'Team test']
//
//        );
        $data = array();
        $nameTeam = $request->request->get('nameTeam');
        $nameTeamFormat = strtoupper(str_replace(' ', '', $nameTeam));
        $user = $this->userRepository->find($id);

        $teams = $user->getTeams()->toArray();

        for($i=0; $i<count($teams); $i++)
        {
            if($nameTeamFormat == strtoupper(str_replace(' ', '',$teams[$i]->getName())))
            {
                $data['result'] = 'AlreadyTeamWithSameName';
            }
        }

        if(!$data)
        {
            $team = new Team();
            $team->setName($nameTeam);
            $this->em->persist($team);
            $team->addUser($user);
            $this->em->flush();
            $data['result'] = 'newTeamCreate';
        }

        return new JsonResponse($data);
//        return $this->render('base.html.twig');

    }
    public function deleteTeam()
    {

    }

    /**
     * @Route("/team/{id}/adduser")
     * @param $id Team id
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addUserInATeam($id, Request $request) : Response
    {
//        $request = Request::create(
//            '/team/{id}/adduser',
//            'POST',
//            ['mail' => 'michel.aimee@live.com']
//
//        );
        $data = array();
        $mail = $request->request->get('mail');
        $team = $this->teamRepository->find($id);
        $user = $this->userRepository->findUserByMail($mail);

        if($team)
        {
            if($user) // Utilisateur inscrit
            {
                $teams = $user->getTeams()->toArray(); // Array d'objet Team avec toutes les teams de l'utilisateur
                dump($teams);
                if(in_array($team, $teams)) // Est ce que l'utilisateur fait deja partie de la team ou on veut l'ajouter ?
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
            }
        } else
        {
            $data['result'] = 'unknownTeam';
        }
        return new JsonResponse($data);
//        return $this->render('base.html.twig');

    }

    /**
     * @Route("/team/{id}/deleteuser")
     * @param $id Team Id
     * @param Request $request
     * @return Response
     */
    public function removeUserInTeam($id , Request $request)
    {
//        $request = Request::create(
//            '/team/{id}/adduser',
//            'POST',
//            ['idUser' => '11']
//
//        );
        $data = array();
        $idUser = $request->request->get('idUser');
        $team = $this->teamRepository->find($id);
        $user = $this->userRepository->find($idUser);

        if($team)
        {
            if($user)
            {
                $teams = $user->getTeams()->toArray(); // Array d'objet Team avec toutes les teams de l'utilisateur
                if(in_array($team, $teams))
                {
                    $team->removeUser($user);
                    $this->em->flush();
                    $data['result'] = 'removeUser';
                }
            }
        }

        return new JsonResponse($data);
//        return $this->render('base.html.twig');
    }

    /**
     * @Route("/team/{id}", name="display.team")
     * @param $id User Id
     * @return Response
     */
    public function verifyIfYouHaveATeam($id) : Response
    {
        $tableTeams = "";
        $selectTeams = "";
        $user = $this->userRepository->find($id);
        if($user)
        {
            $teams = $user->getTeams()->toArray(); // Array d'objet Team avec toutes les teams de l'utilisateur

            if(count($teams) != 0 )
            {

                $selectTeams .= "<option value=''> Choisir une équipe </option>" ;
                for($i=0; $i<count($teams); $i++) // Pour chacune des equipes de l'utilisateur
                {

                    $selectTeams .= "<option value=". $teams[$i]->getId() . ">" . $teams[$i]->getName() . "</option>" ;

                    $tableTeams .= "<div id='listTeams'>";
                    $tableTeams .= "<div class='nameTeam'>" . $teams[$i]->getName() . "</div>";
                    $tableTeams .= "<table data-role='table' id='table-column-toggle' data-mode='columntoggle' class='ui-responsive table-stroke'>";
                    $tableTeams .= "<tbody id=team". $teams[$i]->getId().">";

                    $users = $teams[$i]->getUser()->toArray(); // Tableau d'objet User

                    for($j=0; $j<count($users); $j++)
                    {

                        $tableTeams .= "<tr id='deleteLine-". $teams[$i]->getId() . "-". $users[$j]->getId() . "'>";
                        $tableTeams .= "<td class='user'>". $users[$j]->getName(). "</td>";
                        $tableTeams .= "<td class='cross'><img src='img/delete_min.png' id='deleteFriend-".
                            $teams[$i]->getId
                            () . "-"
                            .$users[$j]->getId()."' class='imgDelete'/></td>";
                        $tableTeams .= "</tr>";

                    }
                    $tableTeams .= "</tbody>";
                    $tableTeams .= "</table>";
                    $tableTeams .= "</div>";

                }
            } else {
                $tableTeams .= "<div id='listTeams'>";
                $tableTeams .= "Vous n'avez aucune équipe";
                $tableTeams .= "</div>";

            }
            $tableTeams .= $selectTeams;
        }
         return new Response($tableTeams);
    }

}