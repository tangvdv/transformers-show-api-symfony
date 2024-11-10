<?php
// src/Controller/Bot/UpdateBot.php
namespace App\Controller\Bot;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\MembershipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Membership;
use App\Repository\FactionRepository;
use App\Repository\ShowRepository;
use App\Repository\EntityRepository;
use App\Normalizer\Bot\CreateUpdateBotNormalizer;
use App\Repository\ScreenTimeRepository;
use Symfony\Component\ExpressionLanguage\Expression;

class UpdateBot extends BotController
{
    #[Route(
        '/api/bots/{id}',
        name: 'update_bot',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager, MembershipRepository $membershipRepository, FactionRepository $factionRepository, ShowRepository $showRepository, EntityRepository $entityRepository, ScreenTimeRepository $screenTimeRepository): Response
    {
        $bot = $this->botRepository->findOneWithParams(array("id" => $id));

        if(!$bot){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }

        $payload = $request->getPayload();
        $params = [
            "entityId" => [
                "value" =>  $payload->get("entityId"),
                "default" => $bot->getEntity()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "showId" => [
                "value" => $payload->get("showId"),
                "default" => $bot->getShow()->getId(),
                "type" => "integer",
                "nullable" => true
            ],
            "description" => [
                "value" =>  $payload->get("description"),
                "default" => "",
                "type" => "string",
                "nullable" => true,
                "method" => "setDescription"
            ],
            "image" => [
                "value" =>  $payload->get("image"),
                "default" => "",
                "type" => "string",
                "nullable" => true,
                "method" => "setImage"
            ],
            "screen_time" => [
                "value" =>  $payload->get("screen_timeId"),
                "type" => "integer",
                "nullable" => true
            ],
            "alt_to_robot" => [
                "value" => $payload->get("alt_to_robot_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true,
                "method" => "setAltToRobot"
            ],
            "robot_to_alt" => [
                "value" => $payload->get("robot_to_alt_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true,
                "method" => "setRobotToAlt"
            ],
            "death_count" => [
                "value" => $payload->get("death_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true,
                "method" => "setDeathCount"
            ],
            "kill_count" => [
                "value" => $payload->get("kill_count"),
                "default" => 0,
                "type" => "integer",
                "nullable" => true,
                "method" => "setKillCount"
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
                    if(array_key_exists("default", $value)){
                        $value["value"] = $value["default"];
                    }
                }
            }
            else{
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
                    else{
                        if(array_key_exists("method", $value)){
                            $method = $value["method"];
                            $bot->$method($value["value"]);
                        }
                    }
                }
            }
        }

        if($params["screen_time"]["value"] !== null){
            $screen_time = $screenTimeRepository->find($params["screen_time"]["value"]);
            if($screen_time === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Screen Time not found");
            }
            else{
                $bot->setScreenTime($screen_time);
            }
        }
        

        if($params["entityId"]["value"] !== $params["entityId"]["default"] && $params["showId"]["value"] !== $params["showId"]["default"]){
          $entity = $entityRepository->find($params["entityId"]["value"]);
            if($entity === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
            }

            $show = $showRepository->find($params["showId"]["value"]);
            if($show === null){
                return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
            }

            if($this->botRepository->findOneBy(array("entity" => $entity, "show" => $show))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An Entity already exist in this Show");
            }
            else{
                $bot->setShow($show)
                    ->setEntity($entity);
            }  
        }
        

        //creates the membership for each given faction
        $preparedMemberships = new ArrayCollection();
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
                    if(!$preparedMemberships->contains($membership)){
                        $preparedMemberships->add($membership);
                    }
                }
            }
        }


        $memberships = $membershipRepository->findAll(array(
            "bot" => $bot
        ));

        $currentMemberships = new ArrayCollection();
        $removeMembershipCollection = new ArrayCollection();

        foreach($memberships as $membership){
            $currentMemberships->add($membership->getFaction());
            $removeMembershipCollection->add($membership);
        }

        foreach($preparedMemberships as $preparedMembership){
            $faction = $preparedMembership->getFaction();

            //binds the prepared membership to the bot
            if(!$currentMemberships->contains($faction)){
                $preparedMembership->setBot($bot);
                $entityManager->persist($preparedMembership);

                $bot->addMembership($preparedMembership);
            }
            else{
                foreach($removeMembershipCollection as $membership){
                    //updates the current state of each membership if there is a difference between prepared and own factions
                    if($membership->getFaction() === $faction){
                        if($membership->getCurrent() !== $preparedMembership->getCurrent()){
                            $membership->setCurrent($preparedMembership->getCurrent());
                            $entityManager->persist($membership);
                        }
                        //removes the current membership from the removing collection
                        $removeMembershipCollection->removeElement($membership);
                    }
                }
            }
        }

        foreach($removeMembershipCollection as $membership){
            $entityManager->remove($membership); 
        }

        $entityManager->persist($bot);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $bot], [new CreateUpdateBotNormalizer]);
    }
}