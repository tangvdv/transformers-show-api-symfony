<?php

namespace App\Normalizer\Character;

use App\Entity\Entity;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CharacterCompactedNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {          
        $json = [
            "id" => $object->getId(),
            "name" => $object->getEntityName(),
            "faction" => []
        ];

        if($object->getHumans()->count() > 0){
            $json["type"] = "human";
        }

        if($object->getBots()->count() > 0){
            $json["type"] = "bot";

            foreach($object->getBots() as $bot){
                foreach($bot->getMemberships() as $membership){
                    if(!in_array($membership->getFaction()->getFactionName() ,$json["faction"])){
                        array_push($json["faction"], $membership->getFaction()->getFactionName());
                    }
                }
            }
        }

        return $json;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return $data instanceof Entity && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Entity::class => true
        ];
    }
}