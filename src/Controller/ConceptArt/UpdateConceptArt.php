<?php
// src/Controller/ConceptArt/UpdateConceptArt.php
namespace App\Controller\ConceptArt;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ShowRepository;
use App\Repository\EntityRepository;
use App\Normalizer\ConceptArt\CreateUpdateConceptArtNormalizer;

class UpdateConceptArt extends ConceptArtController
{
    #[Route(
        '/api/conceptarts/{id}',
        name: 'update_concept_art',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager, ShowRepository $showRepository, EntityRepository $entityRepository): Response
    {
        $conceptart = $this->conceptArtRepository->findOneWithParams(array("id" => $id));

        if(!$conceptart){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Concept Art not found");
        }
        $payload = $request->getPayload();
        $params = [
            "entityId" => [
                "value" =>  $payload->get("entityId"),
                "default" => $conceptart->getEntity()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "showId" => [
                "value" => $payload->get("showId"),
                "default" => $conceptart->getShow()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "title" => [
                "value" => $payload->get("title"),
                "type" => "string",
                "nullable" => true,
                "method" => "setTitle"
            ],
            "image" => [
                "value" => $payload->get("image"),
                "type" => "string",
                "nullable" => true,
                "method" => "setImage"
            ],
            "note" => [
                "value" => $payload->get("note"),
                "type" => "string",
                "nullable" => true,
                "method" => "setNote"
            ],
            "srclink" => [
                "value" =>  $payload->get("srclink"),
                "type" => "string",
                "nullable" => true,
                "method" => "setSrcLink"
            ],
            "date" => [
                "value" =>  $payload->get("date"),
                "type" => "string",
                "nullable" => true,
                "method" => "setDate"
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
                        $conceptart->$method($value["value"]);
                    }
                }
            }
        }

        $showModified = false;
        $entityModified = false;

        $show = $conceptart->getShow();
        if($params["showId"]["value"] !== $params["showId"]["default"]){
            $show = $showRepository->find($params["showId"]["value"]);
            if($show === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
            }
            $showModified = true;
        }

        $entity = $conceptart->getEntity();
        if($params["entityId"]["value"] !== $params["entityId"]["default"]){
            $entity = $entityRepository->find($params["entityId"]["value"]);
            if($entity === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
            }
            $entityModified = true;
        }

        if($showModified || $entityModified){
            $conceptart->setShow($show)
                ->setEntity($entity);
        }
    
        $entityManager->persist($conceptart);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $conceptart], [new CreateUpdateConceptArtNormalizer]);
    }
}