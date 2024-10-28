<?php
// src/Controller/Bot/CreateBot.php
namespace App\Controller\Bot;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Bot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\EntityRepository;
use App\Repository\ShowRepository;
use App\Normalizer\Bot\CreateUpdateBotNormalizer;
use App\Repository\FactionRepository;
use App\Entity\Membership;
use Doctrine\Common\Collections\ArrayCollection;

class CreateBot extends BotController
{
    #[Route(
        '/api/bot',
        name: 'create_bot',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager, EntityRepository $entityRepository, ShowRepository $showRepository, FactionRepository $factionRepository): Response
    {
        $payload = $request->getPayload();
        $params = [
            "entityId" => [
                "value" =>  $payload->get("entityId"),
                "type" => "integer",
                "nullable" => false
            ],
            "showId" => [
                "value" => $payload->get("showId"),
                "type" => "integer",
                "nullable" => false
            ],
            "description" => [
                "value" =>  $payload->get("description"),
                "default" => "",
                "type" => "string",
                "nullable" => true
            ],
            "image" => [
                "value" =>  $payload->get("image"),
                "default" => "",
                "type" => "string",
                "nullable" => true
            ],
            "screen_time" => [
                "value" =>  $payload->get("screen_time"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true
            ],
            "alt_to_robot" => [
                "value" => $payload->get("alt_to_robot_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true
            ],
            "robot_to_alt" => [
                "value" => $payload->get("robot_to_alt_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true
            ],
            "death_count" => [
                "value" => $payload->get("death_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true
            ],
            "kill_count" => [
                "value" => $payload->get("kill_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true
            ],
            "faction" => [
                "value" => $payload->getNonScalar("faction"),
                "default" => [],
                "type" => "array",
                "params" => [
                    "name" => [
                        "type" => "string",
                        "nullable" => false
                    ],
                    "current" => [
                        "type" => "boolean",
                        "nullable" => false
                    ],
                ],
                "nullable" => true
            ]
        ];

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Parameter `{$key}` is missing");
                }
                else{
                    $value["value"] = $value["default"];
                }
            }
            if(gettype($value["value"]) != $value["type"]){
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
            }
            else{
                if($value["type"] === "array" && count($value["value"]) > 0){
                    foreach($value["value"] as $arr){
                        foreach($value["params"] as $key => &$val){
                            if(!array_key_exists($key, $arr)){
                                if(!$value["params"][$key]["nullable"]){
                                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Parameter `{$key}` is missing");
                                }
                                else{
                                    $val = $value["params"][$key]["default"];
                                }
                            }

                            if(gettype($arr[$key]) != $value["params"][$key]["type"]){
                                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["params"][$key]["type"]}` is needed");
                            }
                        }
                    }
                }
            }
        }
        
        $entity = $entityRepository->find($params["entityId"]["value"]);
        if($entity === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
        }
        
        $show = $showRepository->find($params["showId"]["value"]);
        if($show === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        $memberships = new ArrayCollection();
        if(count($params["faction"]["value"]) > 0){
            foreach($params["faction"]["value"] as $arr){
                $faction = $factionRepository->findOneBy(array("faction_name" => $arr["name"]));
                if($faction === null){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Faction not found");
                }
                else{
                    $membership = new Membership();
                    $membership->setFaction($faction)
                            ->setCurrent($arr["current"]);
                    if(!$memberships->contains($membership)){
                        $memberships->add($membership);
                    }
                }
            }
        }

        if($this->botRepository->findOneBy(
            array(
                "entity" => $entity, 
                "show" => $show
        ))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "This Bot already exist with those parameters");
        }

        $transformation_count = $params["alt_to_robot"]["value"] + $params["robot_to_alt"]["value"];
        $bot = new Bot();
        $bot->setDescription($params["description"]["value"])
            ->setImage($params["image"]["value"])
            ->setTransformationCount($transformation_count)
            ->setAltToRobot($params["alt_to_robot"]["value"])
            ->setRobotToAlt($params["robot_to_alt"]["value"])
            ->setDeathCount($params["death_count"]["value"])
            ->setKillCount($params["kill_count"]["value"])
            ->setEntity($entity)
            ->setShow($show);

        $entityManager->persist($bot);
        $entityManager->flush();

        if(count($memberships) > 0){
            foreach($memberships as $membership){
                $membership->setBot($bot);
                $entityManager->persist($membership);
                $entityManager->flush();
            }
        }

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $bot], [new CreateUpdateBotNormalizer]);
    }
}