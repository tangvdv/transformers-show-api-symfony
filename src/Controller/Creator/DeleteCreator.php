<?php
// src/Controller/Creator/DeleteCreator.php
namespace App\Controller\Creator;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteCreator extends CreatorController
{
    #[Route(
        '/api/creators/{id}',
        name: 'delete_creator',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $creator = $this->creatorRepository->findOneWithParams(array("id" => $id));

        if($creator){
            $entityManager->remove($creator);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Creator not found");
        }
    }
}