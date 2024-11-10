<?php
// src/Controller/VoiceActor/DeleteVoiceActor.php
namespace App\Controller\VoiceActor;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class DeleteVoiceActor extends VoiceActorController
{
    #[Route(
        '/api/voiceactors/{id}',
        name: 'delete_voice_actor',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $voiceactor = $this->voiceactorRepository->findOneWithParams(array("id" => $id));

        if($voiceactor){
            $entityManager->remove($voiceactor);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Voice Actor not found");
        }
    }
}