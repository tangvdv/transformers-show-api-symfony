<?php
// src/Controller/Entity/EntityController.php
namespace App\Controller\Entity;

use App\Controller\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\EntityRepository;

abstract class EntityController extends AbstractController
{
    protected EntityRepository $entityRepository;
    protected SerializerInterface $serializer;
    protected ResponseHandler $responseHandler;

    public function __construct(EntityRepository $entityRepository, SerializerInterface $serializer, ResponseHandler $responseHandler)
    {
        $this->entityRepository = $entityRepository;
        $this->serializer = $serializer;
        $this->responseHandler = $responseHandler;
    }
}