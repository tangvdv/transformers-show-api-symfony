<?php
// src/Controller/User/UserController.php
namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\UserRepository;
use App\Controller\ResponseHandler;

abstract class UserController extends AbstractController
{
    protected UserRepository $userRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;

    }
}