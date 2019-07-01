<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\FriendshipRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function connexion(Request $request, UserPasswordEncoderInterface $encoder ): Response
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
            $user = $this->repository->findUserByMail($mail);
            if(!($user))
            {
                $data['result'] = 'UnknowMail';
            } else
            {
                if($encoder->isPasswordValid($user, $password)) // Le password est bon
                {
                    $data['result'] = 'Success';
                    $data['name'] = $user->getName();
                    $data['userId'] = $user->getId();
                } else  // Password incorrect
                {
                    $data['result'] = 'WrongPassword';
                }
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @Route("/inscription", name="user.inscription")
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function inscription(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
//        $request = Request::create(
//            '/inscription',
//            'POST',
//            ['name' => 'Vincent' , 'mail' => 'test@gmail.com' , 'password' => 'admin']
//        );

        $name = $request->request->get('name');
        $mail = $request->request->get('mail');
        $password = $request->request->get('password');

        $data = array();

        if(isset($name) && isset($mail) && isset($password))
        {
            $userExist = $this->repository->findUserByMail($mail);
            if(!($userExist)) // Le mail n'existe pas
            {
                $user = new User();
                $encoded = $encoder->encodePassword($user, $password);
                $user->setName($name)
                    ->setPassword($encoded)
                    ->setMail($mail);
                $this->em->persist($user);
                $this->em->flush();
                $data['result'] = 'Success';
                $data['userId'] = $user->getId();
            } else {
                $data['result'] = 'WrongMail';
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/friends/{id}" , name="user.friends")
     * @param $id
     * @param FriendshipRepository $friendRepository
     * @return Response
     */
    public function displayFriends($id, FriendshipRepository $friendRepository) : Response
    {
        $result = $friendRepository->findFriendsById($id);
        dump($result);
        return $this->render('base.html.twig');
//        return new Response("Essai d'affichage des amis d'une personne");
    }
    /**
     * @Route("/addFriends" , name="user.addFriends")
     * @return Response
     */
    public function addFriends() : Response
    {
        // Essai d'ajout d'un ami
        return new Response("Essai d'ajouter un ami");
    }
}