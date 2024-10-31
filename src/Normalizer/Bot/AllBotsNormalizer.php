<?php

namespace App\Normalizer\Bot;

use App\Entity\Bot;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AllBotsNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {        
        $json = [
            "id" => $object->getId(),
            "name" => $object->getEntity() !== null ? $object->getEntity()->getEntityName() : null,
            "description" => $object->getDescription(),
            "image" => $object->getImage(),
            "faction" => [],
            "alt" => [],
            "screen_time" => array_sum(array_map(fn($scene) => $scene->getTimeStamp(), $object->getScenes()->toArray())),
            "show" => []
        ];

        if($object->getShow() !== null){
            $json["show"] = [ 
                "id" => $object->getShow()->getId(),
                "name" => $object->getShow()->getShowName()
            ];
        }

        foreach($object->getmemberships() as $membership){
            $f = [
                "name" => $membership->getFaction()->getFactionName(),
                "current" => $membership->getCurrent() == 1
            ];
            array_push($json["faction"], $f);
        }

        foreach($object->getAlts() as $alt){
            $a = [
                "id" => $alt->getId(),
                "name" => $alt->getAltName(),
                "image" => $alt->getImage()
            ];
            array_push($json["alt"], $a);
        }

        return $json;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return $data instanceof Bot && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Bot::class => true
        ];
    }
}