<?php
// src/Controller/ScreenTime/GroupScreenTime.php
namespace App\Controller\ScreenTime;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\ScreenTime\CreateScreenTimeNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ArtefactRepository;
use App\Repository\BotRepository;
use App\Repository\HumanRepository;

class GroupScreenTime extends ScreenTimeController
{
    #[Route(
        '/api/screentimes/{screentimeId}/artefacts/{artefactId}',
        name: 'group_screentime_artefact',
        methods: ['POST'],
        requirements: ['screentimeId' => '\d+', 'artefactId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupToArtefact(int $screentimeId, int $artefactId, EntityManagerInterface $entityManager, ArtefactRepository $artefactRepository): Response
    {
        $artefact = $artefactRepository->find($artefactId);
        $screentime = $this->screenTimeRepository->find($screentimeId);
        if(!$screentime){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Screen Time not found");
        }
        if(!$artefact){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artefact not found");
        }
        else{
            $artefact->setScreenTime($screentime);
            $screentime->addArtefact($artefact);

            $entityManager->persist($screentime);
            $entityManager->persist($artefact);
            $entityManager->flush();
        
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $screentime], [new CreateScreenTimeNormalizer], ["filter" => "artefact"]);
        }
    }

    #[Route(
        '/api/screentimes/{screentimeId}/bots/{botId}',
        name: 'group_screentime_bot',
        methods: ['POST'],
        requirements: ['screentimeId' => '\d+', 'botId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupToBot(int $screentimeId, int $botId, EntityManagerInterface $entityManager, BotRepository $botRepository): Response
    {
        $bot = $botRepository->find($botId);
        $screentime = $this->screenTimeRepository->find($screentimeId);
        if(!$screentime){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Screen Time not found");
        }
        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }
        else{
            $bot->setScreenTime($screentime);
            $screentime->addBot($bot);

            $entityManager->persist($screentime);
            $entityManager->persist($bot);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $screentime], [new CreateScreenTimeNormalizer], ["filter" => "bot"]);
        }
    }

    #[Route(
        '/api/screentimes/{screentimeId}/humans/{humanId}',
        name: 'group_screentime_human',
        methods: ['POST'],
        requirements: ['screentimeId' => '\d+', 'humanId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupToHuman(int $screentimeId, int $humanId, EntityManagerInterface $entityManager, HumanRepository $humanRepository): Response
    {
        $human = $humanRepository->find($humanId);
        $screentime = $this->screenTimeRepository->find($screentimeId);
        if(!$screentime){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Screen Time not found");
        }
        if(!$human){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Human not found");
        }
        else{
            $human->setScreenTime($screentime);
            $screentime->addHuman($human);

            $entityManager->persist($screentime);
            $entityManager->persist($human);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $screentime], [new CreateScreenTimeNormalizer], ["filter" => "human"]);
        }
    }
}