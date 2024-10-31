<?php
// src/Controller/Scene/SceneController.php
namespace App\Controller\Scene;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\SceneRepository;

abstract class SceneController extends AbstractController
{
    protected SceneRepository $sceneRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(SceneRepository $sceneRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->sceneRepository = $sceneRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}