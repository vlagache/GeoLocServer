<?php

namespace App\Controller;

use App\Entity\User;
use DateTime;
use App\Repository\UserRepository;
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
        $data = array();
        $mail = $request->request->get('mail');
        $password = $request->request->get('password');

        if(isset($mail) && isset($password))
        {
            $user = $this->repository->findUserByMail($mail);
            if(!($user))
            {
                $data['result'] = 'UnknownMail';
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
        $name = $request->request->get('name');
        $mail = $request->request->get('mail');
        $password = $request->request->get('password');

        $data = array();

        if(isset($name) && isset($mail) && isset($password))
        {
            $userExist = $this->repository->findUserByMail($mail);
            if(!($userExist)) // Le mail n'existe pas
            {
                date_default_timezone_set('Europe/Paris');
                $user = new User();
                $encoded = $encoder->encodePassword($user, $password);
                $user->setName($name)
                    ->setPassword($encoded)
                    ->setInscriptionDate(new DateTime())
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
     * @Route("/account/{id}")
     * @param $id
     * @return Response
     */
    public function getInfosAboutAccount($id) :Response
    {
        $data = array();
        $user = $this->repository->find($id);
        if($user)
        {
            $data['accountName'] = $user->getName();
            $data['accountMail'] = $user->getMail();
            $data['accountInscriptionDate'] = $user->getInscriptionDate()->format('d-m-Y');
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/account/changemail/{id}")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function changeMail(Request $request ,$id) : Response
    {
        $data = array();
        $user = $this->repository->find($id);
        $newMail = $request->request->get('newMail');
        if($user)
        {
            if($user->getMail() == $newMail)
            {
                $data['result'] = 'sameMail';
            } else
            {
                $user->setMail($newMail);
                $this->em->flush();
                $data['result'] = 'mailChange';
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/account/changepassword/{id}")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param $id
     * @return Response
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder ,$id ) : Response
    {
        $data = array();
        $user = $this->repository->find($id);
        $oldPassword = $request->request->get('oldPassword');
        $newPassword = $request->request->get('newPassword');
        if($user)
        {
            if($encoder->isPasswordValid($user, $oldPassword))
            {
                $encoded = $encoder->encodePassword($user, $newPassword);
                $user->setPassword($encoded);
                $this->em->flush();
                $data['result'] = 'passwordChange';
            } else
            {
                $data['result'] = 'wrongOldPassword';
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/account/delete/{id}")
     * @param $id
     * @return Response
     */
    public function deleteAccount($id) : Response
    {
        $data = array();
        $user = $this->repository->find($id);
        if($user)
        {
            $this->em->remove($user);
            $this->em->flush();
            $data['result'] = 'accountDelete';
        }
        return new JsonResponse($data);
    }
}