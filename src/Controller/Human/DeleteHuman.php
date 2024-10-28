<?php
// src/Controller/Human/DeleteHuman.php
namespace App\Controller\Human;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteHuman extends HumanController
{
    #[Route(
        '/api/humans/{id}',
        name: 'delete_human',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $human = $this->humanRepository->findOneWithParams(array("id" => $id));

        if($human){
            $entityManager->remove($human);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Human not found");
        }
    }
}