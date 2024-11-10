<?php
// src/Controller/Application/ApplicationController.php
namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\ApplicationRepository;
use App\Controller\ResponseHandler;

abstract class ApplicationController extends AbstractController
{
    protected ApplicationRepository $applicationRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(ApplicationRepository $applicationRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->applicationRepository = $applicationRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;

    }
}