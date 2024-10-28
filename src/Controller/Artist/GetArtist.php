<?php
// src/Controller/Artist/GetArtist.php
namespace App\Controller\Artist;

use App\Normalizer\Artist\GetArtistNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetArtist extends ArtistController
{
    #[Route(
        '/api/artist/{id}',
        name: 'get_artist_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function getArtistByID(int $id): Response
    {
        $artist = $this->artistRepository->findOneWithParams(array("id" => $id));
        
        if($artist){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $artist], [new GetArtistNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artist not found");
        }
    }
}