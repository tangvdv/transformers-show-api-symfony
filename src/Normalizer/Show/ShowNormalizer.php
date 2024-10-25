<?php

namespace App\Normalizer\Show;

use App\Entity\Show;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ShowNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {        
        $json = [
            "id" => $object->getId(),
            "name" => $object->getShowName(),
            "description" => $object->getDescription(),
            "release_date" => $object->getReleaseDate(),
            "image" => $object->getImage(),
            "running_time" => $object->getRunningTime(),
            "budget" => $object->getBudget(),
            "box_office" => $object->getBoxOffice(),
            "director" => [],
            "producer" => [],
            "writer" => [],
            "composer" => [],
            "bot" => [],
            "human" => [],
            "concept_art" => [],
            "voice_line" => [],
            "artefact" => []
        ];

        foreach($object->getCreators() as $creator){
            array_push($json[$creator->getCategory()], $creator->getCreatorFirstname()." ".$creator->getCreatorLastname());
        }

        foreach($object->getBots() as $bot){
            $b = [
                "id" => $bot->getId(),
                "name" => $bot->getEntity() !== null ? $bot->getEntity()->getEntityName() : null,
                "image" => $bot->getImage(),
                "description" => $bot->getDescription(),
                "screen_time" => $bot->getScreenTime() ? $bot->getScreenTime()->getTotal() : null,
                "faction" => [],
                "alt" => [],
                "voiceactor" => []
            ];

            
            foreach($bot->getAlts() as $alt){
                $a = [
                    "id" => $alt->getId(),
                    "name" => $alt->getAltName(),
                    "image" => $alt->getImage()
                ];
                array_push($b["alt"], $a);
            }

            foreach($bot->getMemberships() as $membership){
                $f = [
                    "name" => $membership->getFaction()->getFactionName(),
                    "current" => $membership->getCurrent() == 1
                ];
                array_push($b["faction"], $f);
            }

            foreach($bot->getVoiceActors() as $voiceactor){
                $va = [
                    "id" => $voiceactor->getId(),
                    "name" => $voiceactor->getVoiceActorFirstname()." ".$voiceactor->getVoiceActorLastname(),
                    "image" => $voiceactor->getImage(),
                    "origin" => $voiceactor->getNationality() !== null ? $voiceactor->getNationality()->getCountry() : null
                ];
                array_push($b["voiceactor"], $va);
            }

            array_push($json["bot"], $b);
        }

        foreach($object->getHumans() as $human){
            $h = [
                "id" => $human->getId(),
                "name" => $human->getEntity() !== null ? $human->getEntity()->getEntityName() : null,
                "image" => $human->getEntity() !== null ? $human->getEntity()->getImage() : null,
                "screen_time" => $human->getScreenTime() ? $human->getScreenTime()->getTotal() : null,
                "actor" => []
            ];
            if($human->getActor() !== null){
                $h["actor"] = [
                    "name" => $human->getActor()->getActorFirstname()." ".$human->getActor()->getActorLastname(),
                    "origin" => $human->getActor()->getNationality() !== null ? $human->getActor()->getNationality()->getCountry() : null
                ];
            }

            array_push($json["human"], $h);
        }

        foreach($object->getConceptArts() as $concept_art){
            $ca = [
                "id" => $concept_art->getId(),
                "name" => $concept_art->getTitle(),
                "note" => $concept_art->getNote(),
                "image" => $concept_art->getImage(),
                "artist" => [],
                "srclink" => $concept_art->getSrclink(),
                "date" => $concept_art->getDate(),
                "character" => []
            ];

            if($concept_art->getEntity() !== null){
                $h["character"] = [
                    "id" => $concept_art->getEntity()->getId(),
                    "name" => $concept_art->getEntity()->getEntityName()
                ];
            }

            foreach($concept_art->getArtists() as $artist){
                $a = [
                    "name" => $artist->getArtistFirstname()." ".$artist->getArtistLastname(),
                    "portfolio_link" => $artist->getPortfolioLink()
                ];

                array_push($ca["artist"], $a);
            }

            array_push($json["concept_art"], $ca);
        }

        foreach($object->getVoiceLines() as $voice_line){
            $vl = [
                "order_number" => $voice_line->getNumber(),
                "content" => $voice_line->getContent(),
                "character" => [
                    "id" => $voice_line->getEntity()->getId(),
                    "name" => $voice_line->getEntity()->getEntityName()
                ] 
            ];
            array_push($json["voice_line"], $vl);
        }

        foreach($object->getArtefacts() as $artefact){
            $a = [
                "id" => $artefact->getId(),
                "name" => $artefact->getEntity() !== null ? $artefact->getEntity()->getEntityName() : null,
                "image" => $artefact->getImage(),
                "screen_time" => $artefact->getScreenTime() ? $artefact->getScreenTime()->getTotal() : null,
            ];
            array_push($json["artefact"], $a);
        }

        return $json;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []):bool
    {
        return $data instanceof Show && $format == 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Show::class => true
        ];
    }
}