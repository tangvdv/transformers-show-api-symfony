<?php
// src/Controller/ScreenTime/CreateScreenTime.php
namespace App\Controller\ScreenTime;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ScreenTime;
use App\Normalizer\ScreenTime\CreateScreenTimeNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ArtefactRepository;
use App\Repository\BotRepository;
use App\Repository\HumanRepository;

class CreateScreenTime extends ScreenTimeController
{
    #[Route(
        '/api/screentime/artefact/{artefactId}',
        name: 'create_screentime_artefact',
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
            $screentime = $this->prepareScreentime($request);
            if(get_class($screentime) === Response::class){
                return $screentime;
            }

            $artefact->setScreenTime($screentime);
            $screentime->addArtefact($artefact);

            $entityManager->persist($screentime);
            $entityManager->persist($artefact);
            $entityManager->flush();
        
            return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $screentime], [new CreateScreenTimeNormalizer], ["filter" => "artefact"]);
        }
    }

    #[Route(
        '/api/screentime/bot/{botId}',
        name: 'create_screentime_bot',
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
            $screentime = $this->prepareScreentime($request);
            if(get_class($screentime) === Response::class){
                return $screentime;
            }

            $bot->setScreenTime($screentime);
            $screentime->addBot($bot);

            $entityManager->persist($screentime);
            $entityManager->persist($bot);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $screentime], [new CreateScreenTimeNormalizer], ["filter" => "bot"]);
        }
    }

    #[Route(
        '/api/screentime/human/{humanId}',
        name: 'create_screentime_human',
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
            $screentime = $this->prepareScreentime($request);
            if(get_class($screentime) === Response::class){
                return $screentime;
            }

            $human->setScreenTime($screentime);
            $screentime->addHuman($human);

            $entityManager->persist($screentime);
            $entityManager->persist($human);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $screentime], [new CreateScreenTimeNormalizer], ["filter" => "human"]);
        }
    }

    public function prepareScreentime(Request $request): mixed
    {
        $payload = $request->getPayload();
        $params = [
            "hour" => [
                "value" =>  $payload->get("hour"),
                "type" => "integer",
                "nullable" => false,
                "method" => "setHour"
            ],
            "minute" => [
                "value" => $payload->get("minute"),
                "type" => "integer",
                "nullable" => false,
                "method" => "setMinute"
            ],
            "second" => [
                "value" => $payload->get("second"),
                "type" => "integer",
                "nullable" => false,
                "method" => "setSecond"
            ]
        ];

        $screentime = new ScreenTime();

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
                        $screentime->$method($value["value"]);
                    }
                }
            }
        }

        $hour = $params["hour"]["value"] * 3600;
        $minute = $params["minute"]["value"] * 60;
        $total = $hour + $minute + $params["second"]["value"];

        $screentime->setTotal($total);

        return $screentime;
    }
}