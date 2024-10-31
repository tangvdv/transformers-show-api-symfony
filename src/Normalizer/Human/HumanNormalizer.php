<?php

namespace App\Normalizer\Human;

use App\Entity\Human;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HumanNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {        
        $json = [
            "id" => $object->getId(),
            "name" => $object->getEntity() !== null ? $object->getEntity()->getEntityName() : null,
            "image" => $object->getImage(),
            "actor" => [],
            "screen_time" => array_sum(array_map(fn($scene) => $scene->getTimeStamp(), $object->getScenes()->toArray())),
            "show" => []
        ];

        return $json;

        if($object->getShow() !== null){
            $json["show"] = [ 
                "id" => $object->getShow()->getId(),
                "name" => $object->getShow()->getShowName()
            ];
        }

        if($object->getActor() !== null){
            $json["actor"] = [ 
                "name" => $object->getActor()->getActorFirstname()." ".$object->getActor()->getActorLastname(),
                "origin" => $object->getActor()->getNationality() !== null ? $object->getActor()->getNationality()->getCountry() : null
            ];
        }
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return $data instanceof Human && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Human::class => true
        ];
    }
}