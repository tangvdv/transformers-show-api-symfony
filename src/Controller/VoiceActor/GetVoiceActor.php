<?php
// src/Controller/VoiceActor/GetVoiceActor.php
namespace App\Controller\VoiceActor;

use App\Normalizer\VoiceActor\VoiceActorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetVoiceActor extends VoiceActorController
{
    #[Route(
        '/api/voiceactors/{id}',
        name: 'get_voice_actor_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getVoiceActorByID(int $id): Response
    {
        $voice_actor = $this->voiceactorRepository->findOneWithParams(array("id" => $id));
        return $this->response($voice_actor);
    }

    #[Route(
        '/api/voiceactors/{name}',
        name: 'get_voice_actor_name',
        methods: ['GET'],
        requirements: ['name' => '\w+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getVoiceActorByName(string $name): Response
    {
        $voiceactor = $this->voiceactorRepository->findOneWithParams(array("name" => $name));
        return $this->response($voiceactor);
    }

    private function response(mixed $voiceactor): Response
    {
        if($voiceactor){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $voiceactor], [new VoiceActorNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Voice Actor not found");
        }
    }
}