<?php
// src/Controller/Actor/UpdateActor.php
namespace App\Controller\Actor;

use App\Normalizer\Actor\CreateUpdateActorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\NationalityRepository;

class UpdateActor extends ActorController
{
    #[Route(
        '/api/actor/{id}',
        name: 'update_actor',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager, NationalityRepository $nationalityRepository): Response
    {
        $actor = $this->actorRepository->findOneWithParams(array("id" => $id));

        if(!$actor){
            return new Response(json_encode([
                "Error" => [
                    "code" => 404,
                    "message" => "Couldn't find any data."
                ]]), 404, ['Content-Type', 'application/json']
            );
        }
        $payload = $request->getPayload();
        $params = [
            "first_name" => [
                "value" =>  $payload->get("first_name"),
                "default" => $actor->getActorFirstname(),
                "type" => "string",
                "nullable" => true
            ],
            "last_name" => [
                "value" => $payload->get("last_name"),
                "default" => $actor->getActorLastname(),
                "type" => "string",
                "nullable" => true
            ],
            "nationalityId" => [
                "value" => $payload->get("nationalityId"),
                "type" => "integer",
                "nullable" => true
            ],
            "image" => [
                "value" =>  $payload->get("image"),
                "default" => "",
                "type" => "string",
                "nullable" => true,
                "method" => "setImage"
            ]
        ];

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Parameter `{$key}` is missing");
                }
                else{
                    if(array_key_exists("default", $value)){
                        $value["value"] = $value["default"];
                    }
                }
            }
            else{
                if(gettype($value["value"]) != $value["type"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
                }
                else{
                    if(array_key_exists("method", $value)){
                        $method = $value["method"];
                        $actor->$method($value["value"]);
                    }
                }
            }
        }
        
        if($params["first_name"]["value"] !== $params["first_name"]["default"] || $params["last_name"]["value"] !== $params["last_name"]["default"]){
            if($this->actorRepository->findOneBy(array("actor_firstname" => $params["first_name"]["value"], "actor_lastname" => $params["last_name"]["value"]))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An actor with this name already exist");
            }
            $actor->setActorFirstname($params["first_name"]["value"])
                ->setActorLastname($params["last_name"]["value"]);
        }

        if($params["nationalityId"]["value"] !== null){
            $nationality = $nationalityRepository->find($params["nationalityId"]["value"]);
            if($nationality === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Nationality not found");
            }
            $actor->setNationality($nationality);
        }

        $entityManager->persist($actor);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $actor], [new CreateUpdateActorNormalizer]);
    }
}