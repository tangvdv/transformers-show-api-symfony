<?php
// src/Controller/Alt/GroupAltBot.php
namespace App\Controller\Alt;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Alt\AltNormalizer;
use App\Repository\BotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GroupAltBot extends AltController
{
    #[Route(
        '/api/alt/{altId}/{botId}',
        name: 'group_alt_bot',
        methods: ['POST'],
        requirements: ['altId' => '\d+', 'botId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupBotToAlt(int $altId, int $botId, BotRepository $botRepository, EntityManagerInterface $entityManager): Response
    {
        $alt = $this->altRepository->find($altId);
        if(!$alt){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Alt not found");
        }

        $bot = $botRepository->find($botId);
        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }

        $bots = $alt->getBots();
        if($bots->contains($bot)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "This Alt is already linked to this Bot");
        }
        else{
            $alt->addBot($bot);
            $entityManager->persist($alt);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $alt], [new AltNormalizer]);
        }
    }

    #[Route(
        '/api/alt/{altId}/{botId}',
        name: 'ungroup_alt_bot',
        methods: ['DELETE'],
        requirements: ['altId' => '\d+', 'botId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function removeBotFromAlt(int $altId, int $botId, BotRepository $botRepository, EntityManagerInterface $entityManager): Response
    {
        $alt = $this->altRepository->find($altId);
        if(!$alt){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Alt not found");
        }

        $bot = $botRepository->find($botId);
        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "TBot not found");
        }

        $bots = $alt->getBots();
        if(!$bots->contains($bot)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "No link was found between this Alt and This Bot");
        }
        else{
            $alt->removeBot($bot);
            $entityManager->persist($alt);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
    }
}