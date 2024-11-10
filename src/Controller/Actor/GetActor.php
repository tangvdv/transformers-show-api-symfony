<?php
// src/Controller/Actor/GetActor.php
namespace App\Controller\Actor;

use App\Normalizer\Actor\ActorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetActor extends ActorController
{
    #[Route(
        '/api/actors/{id}',
        name: 'get_actor_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getActorByID(int $id): Response
    {
        $actor = $this->actorRepository->findOneWithParams(array("id" => $id));
        return $this->response($actor);
    }

    #[Route(
        '/api/actors/{name}',
        name: 'get_actor_name',
        methods: ['GET'],
        requirements: ['name' => '\w+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getActorByName(string $name): Response
    {
        $actor = $this->actorRepository->findOneWithParams(array("name" => $name));
        return $this->response($actor);
    }

    private function response(mixed $actor): Response
    {
        if($actor){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $actor], [new ActorNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Actor not found");
        }
    }
}