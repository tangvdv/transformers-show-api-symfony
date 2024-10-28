<?php
// src/Controller/Actor/DeleteActor.php
namespace App\Controller\Actor;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteActor extends ActorController
{
    #[Route(
        '/api/actors/{id}',
        name: 'delete_actor',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $actor = $this->actorRepository->findOneWithParams(array("id" => $id));

        if($actor){
            foreach($actor->getHumans() as $human){
                $human->removeActor();
                $entityManager->persist($human);
            }
            $entityManager->remove($actor);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Actor not found");
        }
    }
}