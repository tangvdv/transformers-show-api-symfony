<?php
// src/Controller/Entity/DeleteEntity.php
namespace App\Controller\Entity;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class DeleteEntity extends EntityController
{
    #[Route(
        '/api/entities/{id}',
        name: 'delete_entity',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $entity = $this->entityRepository->findOneBy(array("id" => $id));

        if($entity){
            $entityManager->remove($entity);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
        }
    }
}