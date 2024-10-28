<?php
// src/Controller/VoiceActor/GroupVoiceActorBot.php
namespace App\Controller\VoiceActor;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\VoiceActor\VoiceActorNormalizer;
use App\Repository\BotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GroupVoiceActorBot extends VoiceActorController
{
    #[Route(
        '/api/voiceactor/{voiceactorId}/{botId}',
        name: 'group_voice_actor_bot',
        methods: ['POST'],
        requirements: ['voiceactorId' => '\d+', 'botId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupBotToVoiceActor(int $voiceactorId, int $botId, BotRepository $botRepository, EntityManagerInterface $entityManager): Response
    {
        $voiceactor = $this->voiceactorRepository->find($voiceactorId);
        if(!$voiceactor){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Voice Actor not found");
        }

        $bot = $botRepository->find($botId);
        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }

        $bots = $voiceactor->getBots();
        if($bots->contains($bot)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "This Bot is already linked to this Voice Actor");
        }
        else{
            $voiceactor->addBot($bot);
            $entityManager->persist($voiceactor);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $voiceactor], [new VoiceActorNormalizer]);
        }
    }

    #[Route(
        '/api/voiceactor/{voiceactorId}/{botId}',
        name: 'ungroup_voice_actor_bot',
        methods: ['DELETE'],
        requirements: ['voiceactorId' => '\d+', 'botId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function removeBotFromVoiceActor(int $voiceactorId, int $botId, BotRepository $botRepository, EntityManagerInterface $entityManager): Response
    {
        $voiceactor = $this->voiceactorRepository->find($voiceactorId);
        if(!$voiceactor){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Voice Actor not found");
        }

        $bot = $botRepository->find($botId);
        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }

        $bots = $voiceactor->getBots();
        if(!$bots->contains($bot)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "No link found between this Voice Actor and this Bot");
        }
        else{
            $voiceactor->removeBot($bot);
            $entityManager->persist($voiceactor);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
    }
}