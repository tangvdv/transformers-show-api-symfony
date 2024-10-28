<?php
// src/Controller/ScreenTime/ScreenTimeController.php
namespace App\Controller\ScreenTime;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\ScreenTimeRepository;

abstract class ScreenTimeController extends AbstractController
{
    protected ScreenTimeRepository $screenTimeRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(ScreenTimeRepository $screenTimeRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->screenTimeRepository = $screenTimeRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}