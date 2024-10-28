<?php
// src/Controller/Bot/BotController.php
namespace App\Controller\Bot;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\BotRepository;
use App\Controller\ResponseHandler;

abstract class BotController extends AbstractController
{
    protected BotRepository $botRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(BotRepository $botRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->botRepository = $botRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}