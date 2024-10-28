<?php
// src/Controller/Creator/CreatorController.php
namespace App\Controller\Creator;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\CreatorRepository;
use App\Controller\ResponseHandler;

abstract class CreatorController extends AbstractController
{
    protected CreatorRepository $creatorRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(CreatorRepository $creatorRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->creatorRepository = $creatorRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}