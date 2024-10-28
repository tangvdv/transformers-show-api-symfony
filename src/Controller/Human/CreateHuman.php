<?php
// src/Controller/Human/CreateHuman.php
namespace App\Controller\Human;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Human;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\EntityRepository;
use App\Repository\ShowRepository;
use App\Normalizer\Human\HumanNormalizer;
use App\Repository\ActorRepository;
use App\Repository\ScreenTimeRepository;

class CreateHuman extends HumanController
{
    #[Route(
        '/api/humans',
        name: 'create_human',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager, EntityRepository $entityRepository, ShowRepository $showRepository, ActorRepository $actorRepository, ScreenTimeRepository $screenTimeRepository): Response
    {
        $payload = $request->getPayload();
        $params = [
            "entityId" => [
                "value" =>  $payload->get("entityId"),
                "type" => "integer",
                "nullable" => false
            ],
            "showId" => [
                "value" => $payload->get("showId"),
                "type" => "integer",
                "nullable" => false
            ],
            "actorId" => [
                "value" => $payload->get("actorId"),
                "type" => "integer",
                "nullable" => false
            ],
            "image" => [
                "value" =>  $payload->get("image"),
                "default" => "",
                "type" => "string",
                "nullable" => true
            ],
            "screen_timeId" => [
                "value" =>  $payload->get("screen_timeId"),
                "type" => "integer",
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
        
        $entity = $entityRepository->find($params["entityId"]["value"]);
        if($entity === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
        }
        
        $show = $showRepository->find($params["showId"]["value"]);
        if($show === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        $actor = $actorRepository->find($params["actorId"]["value"]);
        if($actor === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Actor not found");
        }

        $screen_time = null;
        if($params["screen_timeId"]["value"] !== null){
            $screen_time = $screenTimeRepository->find($params["screen_timeId"]["value"]);
            if($screen_time === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Screen Time not found");
            }
        }

        if($this->humanRepository->findOneBy(
            array(
                "entity" => $entity, 
                "show" => $show
        ))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "This Human already exist in this Show");
        }

        $human = new Human();
        $human->setImage($params["image"]["value"])
            ->setEntity($entity)
            ->setShow($show)
            ->setActor($actor);
        if($screen_time !== null){
            $human->setScreenTime($screen_time);
        }

        $entityManager->persist($human);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $human], [new HumanNormalizer]);
    }
}