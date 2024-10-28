<?php
// src/Controller/Human/HumanController.php
namespace App\Controller\Human;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\HumanRepository;

abstract class HumanController extends AbstractController
{
    protected HumanRepository $humanRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(HumanRepository $humanRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->humanRepository = $humanRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}