<?php
// src/Controller/VoiceLine/UpdateVoiceLine.php
namespace App\Controller\VoiceLine;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ShowRepository;
use App\Repository\EntityRepository;
use App\Normalizer\VoiceLine\VoiceLineNormalizer;

class UpdateVoiceLine extends VoiceLineController
{
    #[Route(
        '/api/voiceline/{id}',
        name: 'update_voice_line',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager, ShowRepository $showRepository, EntityRepository $entityRepository): Response
    {
        $voiceline = $this->voiceLineRepository->findOneBy(array("id" => $id));

        if(!$voiceline){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Voice Line not found");
        }
        $payload = $request->getPayload();
        $params = [
            "entityId" => [
                "value" =>  $payload->get("entityId"),
                "default" => $voiceline->getEntity()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "showId" => [
                "value" => $payload->get("showId"),
                "default" => $voiceline->getShow()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "content" => [
                "value" => $payload->get("content"),
                "type" => "string",
                "nullable" => true,
                "method" => "setContent"
            ],
            "number" => [
                "value" => $payload->get("number"),
                "default" => $voiceline->getNumber(),
                "type" => "integer",
                "nullable" => true
            ]
        ];

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND,"Parameter `{$key}` is missing");
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
                        $voiceline->$method($value["value"]);
                    }
                }
            }
        }

        $showModified = false;
        $entityModified = false;
        $numberModified = false;

        $show = $voiceline->getShow();
        if($params["showId"]["value"] !== $params["showId"]["default"]){
            $show = $showRepository->find($params["showId"]["value"]);
            if($show === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
            }
            $showModified = true;
        }

        $entity = $voiceline->getEntity();
        if($params["entityId"]["value"] !== $params["entityId"]["default"]){
            $entity = $entityRepository->find($params["entityId"]["value"]);
            if($entity === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
            }
            $entityModified = true;
        }

        if($params["number"]["value"] !== $params["number"]["default"]){
            $numberModified = true;
        }

        if($showModified || $entityModified || $numberModified){
            if($this->voiceLineRepository->findOneBy(array("show" => $show, "entity" => $entity, "number" => $params["number"]["value"]))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "A Voice Line with this number already exist with this Bot in this Show");
            }
            $voiceline->setNumber($params["number"]["value"])
                ->setShow($show)
                ->setEntity($entity);
        }
    
        $entityManager->persist($voiceline);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $voiceline], [new VoiceLineNormalizer]);
    }
}