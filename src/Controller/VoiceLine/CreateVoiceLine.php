<?php
// src/Controller/VoiceLine/CreateVoiceLine.php
namespace App\Controller\VoiceLine;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\VoiceLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\EntityRepository;
use App\Repository\ShowRepository;
use App\Normalizer\VoiceLine\VoiceLineNormalizer;
use Symfony\Component\ExpressionLanguage\Expression;

class CreateVoiceLine extends VoiceLineController
{
    #[Route(
        '/api/voicelines',
        name: 'create_voice_line',
        methods: ['POST']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager, EntityRepository $entityRepository, ShowRepository $showRepository): Response
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
            "content" => [
                "value" => $payload->get("content"),
                "type" => "string",
                "nullable" => false
            ],
            "number" => [
                "value" => $payload->get("number"),
                "type" => "integer",
                "nullable" => false
            ]
        ];

        $voiceline = new VoiceLine();

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Parameter `{$key}` is missing");
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
        
        $entity = $entityRepository->find($params["entityId"]["value"]);
        if($entity === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
        }
        
        $show = $showRepository->find($params["showId"]["value"]);
        if($show === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        if($this->voiceLineRepository->findOneBy(array("show" => $show, "entity" => $entity, "number" => $params["number"]["value"]))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "A Voice Line with this number already exist with this Bot in this Show");
        }

        $voiceline
            ->setContent($params["content"]["value"])
            ->setNumber($params["number"]["value"])
            ->setEntity($entity)
            ->setShow($show);

        $entityManager->persist($voiceline);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $voiceline], [new VoiceLineNormalizer]);
    }
}