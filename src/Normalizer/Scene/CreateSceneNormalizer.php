<?php

namespace App\Normalizer\Scene;

use App\Entity\Scene;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CreateSceneNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {        
        $json = [
            "id" => $object->getId(),
            "description" => $object->getDescription(),
            "start_time" => $object->getStartTime(),
            "end_time" => $object->getEndTime(),
            "duration" => $object->getDuration(),
            "timestamp" => $object->getTimeStamp()
        ];

        if(array_key_exists("filter", $context)){
            $filter = $context["filter"];
            if($filter === "artefact"){
                $json["artefact"] = [];
                foreach($object->getArtefacts() as $artefact){
                    $a = [
                        "id" => $artefact->getId(),
                        "name" => $artefact->getEntity() !== null ? $artefact->getEntity()->getEntityName() : null,
                        "image" => $artefact->getImage(),
                        "show" => $artefact->getShow() !== null ? $artefact->getShow()->getShowName() : null
                    ];
                    array_push($json["artefact"], $a);
                }
            }
            else if($filter === "bot"){
                $json["bot"] = [];
                foreach($object->getBots() as $bot){
                    $b = [
                        "id" => $bot->getId(),
                        "name" => $bot->getEntity() !== null ? $bot->getEntity()->getEntityName() : null,
                        "image" => $bot->getImage(),
                        "show" => $bot->getShow() !== null ? $bot->getShow()->getShowName() : null
                    ];
                    array_push($json["bot"], $b);
                }
            }
            else if($filter === "human"){
                $json["human"] = [];
                foreach($object->getHumans() as $human){
                    $h = [
                        "id" => $human->getId(),
                        "name" => $human->getEntity() !== null ? $human->getEntity()->getEntityName() : null,
                        "image" => $human->getImage(),
                        "show" => $human->getShow() !== null ? $human->getShow()->getShowName() : null
                    ];
                    array_push($json["human"], $h);
                }
            }
        }

        return $json;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return $data instanceof Scene && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Scene::class => true
        ];
    }
}