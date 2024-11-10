<?php
// src/Controller/Artist/GetAllArtists.php
namespace App\Controller\Artist;

use App\Normalizer\Artist\ArtistNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllArtists extends ArtistController
{
    #[Route(
        '/api/artists',
        name: 'get_artists',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(): Response
    {   
        $artists = $this->artistRepository->findAll();

        if($artists){
            $data = [
                "total" => count($artists),
                "items" => $artists
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new ArtistNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}