<?php
// src/Controller/Artefact/UpdateArtefact.php
namespace App\Controller\Artefact;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ShowRepository;
use App\Repository\EntityRepository;
use App\Normalizer\Artefact\ArtefactNormalizer;
use App\Repository\ScreenTimeRepository;

class UpdateArtefact extends ArtefactController
{
    #[Route(
        '/api/artefacts/{id}',
        name: 'update_artefact',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager, ShowRepository $showRepository, EntityRepository $entityRepository, ScreenTimeRepository $screenTimeRepository): Response
    {
        $artefact = $this->artefactRepository->findOneWithParams(array("id" => $id));

        if(!$artefact){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artefact not found");
        }
        $payload = $request->getPayload();
        $params = [
            "entityId" => [
                "value" =>  $payload->get("entityId"),
                "default" => $artefact->getEntity()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "showId" => [
                "value" => $payload->get("showId"),
                "default" => $artefact->getShow()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "image" => [
                "value" =>  $payload->get("image"),
                "default" => "",
                "type" => "string",
                "nullable" => true,
                "method" => "setImage"
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
                else{
                    if(array_key_exists("method", $value)){
                        $method = $value["method"];
                        $artefact->$method($value["value"]);
                    }
                }
            }
        }

        if($params["screen_timeId"]["value"] !== null){
            $screen_time = $screenTimeRepository->find($params["screen_timeId"]["value"]);
            if($screen_time === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Screen Time not found");
            }
            $artefact->setScreenTime($screen_time);
        }

        $showModified = false;
        $entityModified = false;

        $show = $artefact->getShow();
        if($params["showId"]["value"] !== $params["showId"]["default"]){
            $show = $showRepository->find($params["showId"]["value"]);
            if($show === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
            }
            $showModified = true;
        }

        $entity = $artefact->getEntity();
        if($params["entityId"]["value"] !== $params["entityId"]["default"]){
            $entity = $entityRepository->find($params["entityId"]["value"]);
            if($entity === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
            }
            $entityModified = true;
        }

        if($showModified || $entityModified){
            if($this->artefactRepository->findOneBy(array("entity" => $entity, "show" => $show))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An Entity already exist in this Show");
            }
            $artefact->setShow($show)
                ->setEntity($entity);
        }
    
        $entityManager->persist($artefact);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $artefact], [new ArtefactNormalizer]);
    }
}