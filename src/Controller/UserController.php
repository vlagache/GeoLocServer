<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


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
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function connexion(Request $request, UserPasswordEncoderInterface $encoder ): Response
    {
        $request = Request::create(
            '/inscription',
            'POST',
            ['mail' => 'anonymous@gmail.com' , 'password' => 'anonymous']
        );
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
//                $response->headers->set('Content-Type', 'application/json');
//                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response;
            } else // $mail existe dans la DB
            {
                if($encoder->isPasswordValid($user[0], $password)) // Le password est bon
                {
                    $data['result'] = 'Success';
                    $data['name'] = $user[0]->getName();
                    $response = new Response(json_encode($data));
                    return $response;
                } else  // Password incorrect
                {
                    $data['result'] = 'WrongPassword';
                    $response = new Response(json_encode($data));
                    return $response;
                }
            }
        }
        return $this->render('base.html.twig');
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @Route("/inscription", name="user.inscription")
     */
    public function inscription(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $request = Request::create(
            '/inscription',
            'POST',
            ['name' => 'Anonymous' , 'mail' => 'anonymous@gmail.com' , 'password' => 'anonymous']
        );

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
                $encoded = $encoder->encodePassword($user, $password);
                $user->setName($name)
                    ->setPassword($encoded) // Cryptage Password
                    ->setMail($mail);
                $this->em->persist($user);
                $this->em->flush();
                $data['result'] = 'Success';
                $response = new Response(json_encode($data));
                return $response;
            } else {
                $data['result'] = 'WrongMail';
                $response = new Response(json_encode($data));
                return $response;
            }
        }
    }
}