<?php
// src/Controller/VoiceActor/VoiceActorController.php
namespace App\Controller\VoiceActor;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\VoiceActorRepository;

abstract class VoiceActorController extends AbstractController
{
    protected VoiceActorRepository $voiceactorRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(VoiceActorRepository $voiceactorRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->voiceactorRepository = $voiceactorRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}