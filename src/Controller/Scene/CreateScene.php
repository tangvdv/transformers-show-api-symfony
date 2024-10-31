<?php
// src/Controller/Scene/CreateScene.php
namespace App\Controller\Scene;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Scene;
use App\Normalizer\Scene\CreateSceneNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ArtefactRepository;
use App\Repository\BotRepository;
use App\Repository\HumanRepository;

class CreateScene extends SceneController
{
    #[Route(
        '/api/scenes/artefacts/{artefactId}',
        name: 'create_scene_artefact',
        methods: ['POST'],
        requirements: ['artefactId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function createForArtefact(int $artefactId, Request $request, EntityManagerInterface $entityManager, ArtefactRepository $artefactRepository): Response
    {
        $artefact = $artefactRepository->find($artefactId);
        if(!$artefact){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artefact not found");
        }
        else{
            $scene = $this->prepareScene($request);
            if(get_class($scene) === Response::class){
                return $scene;
            }

            $scene->addArtefact($artefact);

            $entityManager->persist($scene);
            $entityManager->flush();
        
            return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $scene], [new CreateSceneNormalizer], ["filter" => "artefact"]);
        }
    }

    #[Route(
        '/api/scenes/bots/{botId}',
        name: 'create_scene_bot',
        methods: ['POST'],
        requirements: ['botId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function createForBot(int $botId, Request $request, EntityManagerInterface $entityManager, BotRepository $botRepository): Response
    {
        $bot = $botRepository->find($botId);
        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }
        else{
            $scene = $this->prepareScene($request);
            if(get_class($scene) === Response::class){
                return $scene;
            }

            $scene->addBot($bot);

            $entityManager->persist($scene);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $scene], [new CreateSceneNormalizer], ["filter" => "bot"]);
        }
    }

    #[Route(
        '/api/scenes/humans/{humanId}',
        name: 'create_scene_human',
        methods: ['POST'],
        requirements: ['humanId' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function createForHuman(int $humanId, Request $request, EntityManagerInterface $entityManager, HumanRepository $humanRepository): Response
    {
        $human = $humanRepository->find($humanId);
        if(!$human){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Human not found");
        }
        else{
            $scene = $this->prepareScene($request);
            if(get_class($scene) === Response::class){
                return $scene;
            }

            $scene->addHuman($human);

            $entityManager->persist($scene);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $scene], [new CreateSceneNormalizer], ["filter" => "human"]);
        }
    }

    public function prepareScene(Request $request): mixed
    {
        $payload = $request->getPayload();
        $params = [
            "description" => [
                "value" =>  $payload->get("description"),
                "type" => "string",
                "nullable" => true,
                "method" => "setDescription"
            ],
            "start_time" => [
                "value" => $payload->get("start_time"),
                "type" => "string",
                "nullable" => false
            ],
            "end_time" => [
                "value" => $payload->get("end_time"),
                "type" => "string",
                "nullable" => false
            ]
        ];

        $scene = new Scene();

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
                        $scene->$method($value["value"]);
                    }
                }
            }
        }

        if(!$this->isTimeFormat($params["start_time"]["value"])){
            return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Paramater `start_time` is in incorrect time format, `hh:mm:ss` is needed");
        }
        else{
            $scene->setStartTime($params["start_time"]["value"]);
        }

        if(!$this->isTimeFormat($params["end_time"]["value"])){
            return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Paramater `end_time` is in incorrect time format, `hh:mm:ss` is needed");
        }
        else{
            $scene->setEndTime($params["end_time"]["value"]);
        }

        $scene->calculateDuration();

        return $scene;
    }

    private function isTimeFormat(string $time): bool 
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $time) === 1;
    }
}