<?php
// src/Controller/ConceptArt/GroupConceptArtArtist.php
namespace App\Controller\ConceptArt;

use App\Normalizer\ConceptArt\GroupArtistConceptArtNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GroupConceptArtArtist extends ConceptArtController
{
    #[Route(
        '/api/conceptarts/{conceptartId}/artists/{artistId}',
        name: 'group_concept_art_artist',
        methods: ['POST'],
        requirements: ['conceptartId' => '\d+', 'artistId' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function groupArtistToConceptArt(int $conceptartId, int $artistId, ArtistRepository $artistRepository, EntityManagerInterface $entityManager): Response
    {
        $conceptart = $this->conceptArtRepository->find($conceptartId);
        if(!$conceptart){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Concept Art not found");
        }

        $artist = $artistRepository->find($artistId);
        if(!$artist){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        $artists = $conceptart->getArtists();
        if($artists->contains($artist)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "This Artist is already linked to this Concept Art");
        }
        else{
            $conceptart->addArtist($artist);
            $entityManager->persist($conceptart);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $conceptart], [new GroupArtistConceptArtNormalizer]);
        }
    }

    #[Route(
        '/api/conceptarts/{conceptartId}/artists/{artistId}',
        name: 'ungroup_concept_art_artist',
        methods: ['DELETE'],
        requirements: ['conceptartId' => '\d+', 'artistId' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function removeArtistFromConceptArt(int $conceptartId, int $artistId, ArtistRepository $artistRepository, EntityManagerInterface $entityManager): Response
    {
        $conceptart = $this->conceptArtRepository->find($conceptartId);
        if(!$conceptart){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Concept Art not found");
        }

        $artist = $artistRepository->find($artistId);
        if(!$artist){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artist not found");
        }

        $artists = $conceptart->getArtists();
        if(!$artists->contains($artist)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "No link found between this Artist and this Concept Art");
        }
        else{
            $conceptart->removeArtist($artist);
            $entityManager->persist($conceptart);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
    }
}