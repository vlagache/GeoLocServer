<?php


namespace App\Controller;



use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $repository;
    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(UserRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/connexion", name="user.connexion")
     * @return Response
     */
    public function connexion(Request $request): Response
    {
//        $request = Request::create(
//            '/inscription',
//            'POST',
//            ['mail' => 'admin@gmail.com' , 'password' => 'admin']
//        );

        $data = array();
        $mail = $request->request->get('mail');
        $password = $request->request->get('password');

        if(isset($mail) && isset($password))
        {
            $user = $this->repository->findUser($mail);
            if(empty($user)) // Il n'existe aucun User avec $mail
            {
                $data['result'] = 'UnknowMail';
                $response = new Response(json_encode($data));
                $response->headers->set('Content-Type', 'application/json');
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response;

            } else // $mail existe dans la DB
            {
                if($password == $user[0]->getPassword()) // Le password est bon
                {
                    $data['result'] = 'Success';
                    $data['name'] = $user[0]->getName();
                    $response = new Response(json_encode($data));
                    $response->headers->set('Content-Type', 'application/json');
                    $response->headers->set('Access-Control-Allow-Origin', '*');
                    return $response;
                } else  // Password incorrect
                {
                    $data['result'] = 'WrongPassword';
                    $response = new Response(json_encode($data));
                    $response->headers->set('Content-Type', 'application/json');
                    $response->headers->set('Access-Control-Allow-Origin', '*');
                    return $response;
                }
            }
        }
        return $this->render('base.html.twig');
    }
    /**
     * @param Request $request
     * @return Response
     * @Route("/inscription", name="user.inscription")
     */
    public function inscription(Request $request): Response
    {

        $name = $request->request->get('name');
        $mail = $request->request->get('mail');
        $password = $request->request->get('password');

        $data = array();

        if(isset($name) && isset($mail) && isset($password))
        {
            $mailExist = $this->repository->mailExist($mail);
            if(empty($mailExist)) // Le mail n'existe pas
            {
                $user = new User();
                $user->setName($name)
                    ->setPassword($password) // Cryptage Password
                    ->setMail($mail);
                $this->em->persist($user);
                $this->em->flush();
                $data['result'] = 'Success';
                $response = new Response(json_encode($data));
                $response->headers->set('Content-Type', 'application/json');
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response;
            } else {
                $data['result'] = 'WrongMail';
                $response = new Response(json_encode($data));
                $response->headers->set('Content-Type', 'application/json');
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response;
            }
        }
    }
}