<?php
// src/Controller/Actor/ActorController.php
namespace App\Controller\Actor;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\ActorRepository;
use App\Controller\ResponseHandler;

abstract class ActorController extends AbstractController
{
    protected ActorRepository $actorRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(ActorRepository $actorRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->actorRepository = $actorRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}