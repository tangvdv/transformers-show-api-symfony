<?php

namespace App\Normalizer\Application;

use App\Entity\Application;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CreateApplicationNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {        
        $json = [
            "name" => $object->getApplicationName(),
            "description" => $object->getDescription(),
            "client_id" => $object->getClientId(),
            "client_secret" => $object->getPlainClientSecret(),
            "created_at" => $object->getCreatedAt(),
            "updated_at" => $object->getUpdatedAt(),
            "max_request" => $object->getMaxRequest()
        ];

        return $json;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return $data instanceof Application && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Application::class => true
        ];
    }
}