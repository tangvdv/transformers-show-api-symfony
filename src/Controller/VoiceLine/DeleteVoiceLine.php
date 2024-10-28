<?php
// src/Controller/VoiceLine/DeleteVoiceLine.php
namespace App\Controller\VoiceLine;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteVoiceLine extends VoiceLineController
{
    #[Route(
        '/api/voiceline/{id}',
        name: 'delete_voice_line',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $voiceline = $this->voiceLineRepository->findOneBy(array("id" => $id));

        if($voiceline){
            $entityManager->remove($voiceline);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Voice Line not found");
        }
    }
}