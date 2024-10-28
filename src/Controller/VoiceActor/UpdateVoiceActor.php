<?php
// src/Controller/VoiceActor/UpdateVoiceActor.php
namespace App\Controller\VoiceActor;

use App\Normalizer\VoiceActor\CreateUpdateVoiceActorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\NationalityRepository;

class UpdateVoiceActor extends VoiceActorController
{
    #[Route(
        '/api/voiceactor/{id}',
        name: 'update_voice_actor',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager, NationalityRepository $nationalityRepository): Response
    {
        $voiceactor = $this->voiceactorRepository->findOneWithParams(array("id" => $id));

        if(!$voiceactor){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Voice Actor not found");
        }
        $payload = $request->getPayload();
        $params = [
            "first_name" => [
                "value" =>  $payload->get("first_name"),
                "default" => $voice_actor->getVoiceActorFirstname(),
                "type" => "string",
                "nullable" => true
            ],
            "last_name" => [
                "value" => $payload->get("last_name"),
                "default" => $voice_actor->getVoiceActorLastname(),
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
                    return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST,"Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
                }
                else{
                    if(array_key_exists("method", $value)){
                        $method = $value["method"];
                        $voiceactor->$method($value["value"]);
                    }
                }
            }
        }
        
        if($params["first_name"]["value"] !== $params["first_name"]["default"] || $params["last_name"]["value"] !== $params["last_name"]["default"]){
            if($this->voiceactorRepository->findOneBy(array("actor_firstname" => $params["first_name"]["value"], "actor_lastname" => $params["last_name"]["value"]))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An actor with this name already exist");
            }
            $voiceactor->setVoiceActorFirstname($params["first_name"]["value"])
                ->setVoiceActorLastname($params["last_name"]["value"]);
        }

        if($params["nationalityId"]["value"] !== null){
            $nationality = $nationalityRepository->find($params["nationalityId"]["value"]);
            if($nationality === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Nationality not found");
            }
            $voiceactor->setNationality($nationality);
        }

        $entityManager->persist($voiceactor);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $voiceactor], [new CreateUpdateVoiceActorNormalizer]);
    }
}