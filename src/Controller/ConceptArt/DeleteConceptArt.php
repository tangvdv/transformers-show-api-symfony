<?php
// src/Controller/ConceptArt/DeleteConceptArt.php
namespace App\Controller\ConceptArt;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class DeleteConceptArt extends ConceptArtController
{
    #[Route(
        '/api/conceptarts/{id}',
        name: 'delete_concept_art',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $conceptart = $this->conceptArtRepository->findOneWithParams(array("id" => $id));

        if($conceptart){
            $entityManager->remove($conceptart);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Concept Art not found");
        }
    }
}