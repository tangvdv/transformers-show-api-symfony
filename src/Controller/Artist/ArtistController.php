<?php
// src/Controller/Artist/ArtistController.php
namespace App\Controller\Artist;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\ArtistRepository;

abstract class ArtistController extends AbstractController
{
    protected ArtistRepository $artistRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(ArtistRepository $artistRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->artistRepository = $artistRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}