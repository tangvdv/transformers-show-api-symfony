<?php

namespace App\Normalizer\Scene;

use App\Entity\Scene;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SceneNormalizer implements NormalizerInterface
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