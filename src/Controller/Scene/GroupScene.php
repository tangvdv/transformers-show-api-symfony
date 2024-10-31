<?php
// src/Controller/Scene/GroupScene.php
namespace App\Controller\Scene;

use App\Normalizer\Scene\CreateSceneNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ArtefactRepository;
use App\Repository\BotRepository;
use App\Repository\HumanRepository;

class GroupScene extends SceneController
{
    #[Route(
        '/api/scenes/{sceneId}/artefacts/{artefactId}',
        name: 'group_scene_artefact',
        methods: ['POST'],
        requirements: ['sceneId' => '\d+', 'artefactId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupToArtefact(int $sceneId, int $artefactId, EntityManagerInterface $entityManager, ArtefactRepository $artefactRepository): Response
    {
        $artefact = $artefactRepository->find($artefactId);
        $scene = $this->sceneRepository->find($sceneId);
        if(!$scene){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Scene not found");
        }
        if(!$artefact){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artefact not found");
        }
        else{
            foreach($scene->getHumans() as $human){
                $scene->removeHuman($human);
            }
            foreach($scene->getBots() as $bot){
                $scene->removeBot($bot);
            }
            foreach($scene->getArtefacts() as $artefact){
                $scene->removeArtefact($artefact);
            }
            
            $scene->addArtefact($artefact);

            $entityManager->persist($scene);
            $entityManager->flush();
        
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $scene], [new CreateSceneNormalizer], ["filter" => "artefact"]);
        }
    }

    #[Route(
        '/api/scenes/{sceneId}/bots/{botId}',
        name: 'group_scene_bot',
        methods: ['POST'],
        requirements: ['sceneId' => '\d+', 'botId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupToBot(int $sceneId, int $botId, EntityManagerInterface $entityManager, BotRepository $botRepository): Response
    {
        $bot = $botRepository->find($botId);
        $scene = $this->sceneRepository->find($sceneId);
        if(!$scene){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Scene not found");
        }
        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }
        else{
            foreach($scene->getHumans() as $human){
                $scene->removeHuman($human);
            }
            foreach($scene->getBots() as $bot){
                $scene->removeBot($bot);
            }
            foreach($scene->getArtefacts() as $artefact){
                $scene->removeArtefact($artefact);
            }

            $scene->addBot($bot);

            $entityManager->persist($scene);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $scene], [new CreateSceneNormalizer], ["filter" => "bot"]);
        }
    }

    #[Route(
        '/api/scenes/{sceneId}/humans/{humanId}',
        name: 'group_scene_human',
        methods: ['POST'],
        requirements: ['sceneId' => '\d+', 'humanId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function groupToHuman(int $sceneId, int $humanId, EntityManagerInterface $entityManager, HumanRepository $humanRepository): Response
    {
        $human = $humanRepository->find($humanId);
        $scene = $this->sceneRepository->find($sceneId);
        if(!$scene){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Scene not found");
        }
        if(!$human){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Human not found");
        }
        else{
            foreach($scene->getHumans() as $human){
                $scene->removeHuman($human);
            }
            foreach($scene->getBots() as $bot){
                $scene->removeBot($bot);
            }
            foreach($scene->getArtefacts() as $artefact){
                $scene->removeArtefact($artefact);
            }

            $scene->addHuman($human);

            $entityManager->persist($scene);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $scene], [new CreateSceneNormalizer], ["filter" => "human"]);
        }
    }
}