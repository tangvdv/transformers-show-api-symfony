<?php
// src/Controller/Alt/altController.php
namespace App\Controller\Alt;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\AltRepository;
use App\Controller\ResponseHandler;

abstract class AltController extends AbstractController
{
    protected AltRepository $altRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(AltRepository $altRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->altRepository = $altRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}