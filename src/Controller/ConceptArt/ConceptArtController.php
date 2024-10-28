<?php
// src/Controller/ConceptArt/ConceptArtController.php
namespace App\Controller\ConceptArt;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\ConceptArtRepository;

abstract class ConceptArtController extends AbstractController
{
    protected ConceptArtRepository $conceptArtRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(ConceptArtRepository $conceptArtRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->conceptArtRepository = $conceptArtRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}