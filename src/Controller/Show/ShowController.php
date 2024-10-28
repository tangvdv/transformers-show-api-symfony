<?php
// src/Controller/Show/ShowController.php
namespace App\Controller\Show;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\ShowRepository;

abstract class ShowController extends AbstractController
{
    protected ShowRepository $showRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(ShowRepository $showRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->showRepository = $showRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}