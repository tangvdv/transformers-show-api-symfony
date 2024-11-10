<?php
// src/Controller/Actor/CreateActor.php
namespace App\Controller\Actor;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\NationalityRepository;
use App\Entity\Actor;
use App\Normalizer\Actor\CreateUpdateActorNormalizer;
use Symfony\Component\ExpressionLanguage\Expression;

class CreateActor extends ActorController
{
    #[Route(
        '/api/actors',
        name: 'create_actor',
        methods: ['POST']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager, NationalityRepository $nationalityRepository): Response
    {
        $payload = $request->getPayload();
        $params = [
            "first_name" => [
                "value" =>  $payload->get("first_name"),
                "type" => "string",
                "nullable" => false
            ],
            "last_name" => [
                "value" => $payload->get("last_name"),
                "type" => "string",
                "nullable" => false
            ],
            "nationalityId" => [
                "value" => $payload->get("nationalityId"),
                "type" => "integer",
                "nullable" => false
            ],
            "image" => [
                "value" =>  $payload->get("image"),
                "default" => "",
                "type" => "string",
                "nullable" => true
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
            }
        }
        
        $nationality = $nationalityRepository->find($params["nationalityId"]["value"]);
        if(!$nationality){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Nationality not found");
        }

        if($this->actorRepository->findOneBy(
            array(
                "actor_firstname" => $params["first_name"]["value"], 
                "actor_lastname" => $params["last_name"]["value"]
        ))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An actor with this name already exist");
        }

        $actor = new Actor();
        $actor
            ->setActorFirstname($params["first_name"]["value"])
            ->setActorLastname($params["last_name"]["value"])
            ->setNationality($nationality)
            ->setImage($params["image"]["value"]);
        $entityManager->persist($actor);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $actor], [new CreateUpdateActorNormalizer]);
    }
}