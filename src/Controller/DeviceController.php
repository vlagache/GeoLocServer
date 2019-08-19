<?php


namespace App\Controller;


use App\Entity\Device;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeviceController extends AbstractController
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
        $this->em  = $em;
    }

    /**
     * @Route("/device/{id}")
     * @param $id User
     * @param Request $request
     * @return Response
     */
    public function newDevice($id, Request $request) :Response
    {
        $data = array();
        $token = $request->request->get('token');
        $user = $this->repository->find($id);
        if($user)
        {
            $device = new Device();
            $device->setUser($user)
                ->setTokenFirebase($token);
            $this->em->persist($device);
            $this->em->flush();
            $data['result'] = 'DeviceSave';
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/device/checktoken/{id}")
     * @param $id User
     * @param Request $request
     * @return Response
     *
     */
    public function checkDeviceAfterReinstall($id, Request $request) :Response
    {
        $data = array();
        $token = $request->request->get('token');
        $user = $this->repository->find($id);
        if($user)
        {
            $device = $user->getDevice();
            $tokenDevice = $device->getTokenFirebase();
            if($tokenDevice != $token)
            {
                $device->setTokenFirebase($token);
                $this->em->flush();
                $data['result'] = 'newTokenSave';
            }
        }
        return new JsonResponse($data);
    }
}
