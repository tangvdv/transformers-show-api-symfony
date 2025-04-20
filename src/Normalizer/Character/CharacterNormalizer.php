<?php

namespace App\Normalizer\Character;

use App\Entity\Human;
use App\Entity\Bot;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CharacterNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {          
        $json = [
            "id" => $object->getId(),
            "name" => $object->getEntity()->getEntityName(),
            "show" => $object->getShow() !== null ? [
                "id" => $object->getShow()->getId(),
                "name" => $object->getShow()->getShowName() 
                ] : null,
            "faction" => null
        ];

        if($object instanceof Human){
            $json["type"] = "human";
        }

        if($object instanceof Bot){
            $json["type"] = "bot";

            foreach($object->getMemberships() as $membership){
                if($membership->getCurrent() == 1){
                    $json["faction"] = $membership->getFaction()->getFactionName();
                }
            }
        }

        return $json;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return ($data instanceof Human || $data instanceof Bot ) && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Human::class => true,
            Bot::class => true
        ];
    }
}