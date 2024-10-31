<?php

namespace App\Normalizer\Bot;

use App\Entity\Bot;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CreateUpdateBotNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {        
        $json = [
            "id" => $object->getId(),
            "name" => $object->getEntity() !== null ? $object->getEntity()->getEntityName() : null,
            "description" => $object->getDescription(),
            "image" => $object->getImage(),
            "faction" => [],
            "transformation_count" => $object->getTransformationCount(),
            "alt_to_robot_count" => $object->getAltToRobot(),
            "robot_to_alt_count" => $object->getRobotToAlt(),
            "death_count" => $object->getDeathCount(),
            "kill_count" => $object->getKillCount(),
            "screen_time" => array_sum(array_map(fn($scene) => $scene->getTimeStamp(), $object->getScenes()->toArray())),
            "show" => $object->getShow() !== null ? $object->getShow()->getShowName() : null
        ];

        foreach($object->getMemberships() as $membership){
            $f = [
                "name" => $membership->getFaction()->getFactionName(),
                "current" => $membership->getCurrent() == 1
            ];
            array_push($json["faction"], $f);
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