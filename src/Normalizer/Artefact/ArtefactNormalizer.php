<?php

namespace App\Normalizer\Artefact;

use App\Entity\Artefact;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ArtefactNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {        
        $json = [
            "id" => $object->getId(),
            "name" => $object->getEntity() !== null ? $object->getEntity()->getEntityName() : null,
            "image" => $object->getEntity()->getImage(),
            "screen_time" => array_sum(array_map(fn($scene) => $scene->getTimeStamp(), $object->getScenes()->toArray())),
            "show" => []
        ];

        if($object->getShow() !== null){
            $json["show"] = [
                "id" => $object->getShow()->getId(),
                "name" => $object->getShow()->getShowName()
            ];
        }

        return $json;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return $data instanceof Artefact && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Artefact::class => true
        ];
    }
}