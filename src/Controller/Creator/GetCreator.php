<?php
// src/Controller/Creator/GetCreator.php
namespace App\Controller\Creator;

use App\Normalizer\Creator\CreatorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetCreator extends CreatorController
{
    #[Route(
        '/api/creators/{id}',
        name: 'get_creator_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getCreatorByID(int $id): Response
    {
        $creator = $this->creatorRepository->findOneWithParams(array("id" => $id));
        return $this->response($creator);
    }

    #[Route(
        '/api/creators/{name}',
        name: 'get_creator_name',
        methods: ['GET'],
        requirements: ['name' => '\w+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getCreatorByName(string $name): Response
    {
        $creator = $this->creatorRepository->findOneWithParams(array("name" => $name));
        return $this->response($creator);
    }

    private function response(mixed $creator): Response
    {
        if($creator){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $creator], [new CreatorNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Creator not found");
        }
    }
}