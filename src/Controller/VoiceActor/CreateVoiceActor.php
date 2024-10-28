<?php
// src/Controller/VoiceActor/CreateVoiceActor.php
namespace App\Controller\VoiceActor;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\NationalityRepository;
use App\Entity\VoiceActor;
use App\Normalizer\VoiceActor\CreateUpdateVoiceActorNormalizer;

class CreateVoiceActor extends VoiceActorController
{
    #[Route(
        '/api/voiceactors',
        name: 'create_voice_actor',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
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

        if($this->voiceactorRepository->findOneBy(
            array(
                "voiceactor_firstname" => $params["first_name"]["value"], 
                "voiceactor_lastname" => $params["last_name"]["value"]
        ))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "A Voice Actor with this name already exist");
        }

        $voiceactor = new VoiceActor();
        $voiceactor
            ->setVoiceActorFirstname($params["first_name"]["value"])
            ->setVoiceActorLastname($params["last_name"]["value"])
            ->setNationality($nationality)
            ->setImage($params["image"]["value"]);
        $entityManager->persist($voiceactor);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $voiceactor], [new CreateUpdateVoiceActorNormalizer]);
    }
}