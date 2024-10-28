<?php
// src/Controller/Show/GetAllShows.php
namespace App\Controller\Show;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Show\AllShowsNormalizer;

class GetAllShows extends ShowController
{
    #[Route(
        '/api/shows',
        name: 'get_shows',
        methods: ['GET']
    )]
    public function __invoke(): Response
    {
        $shows = $this->showRepository->findAll();
        
        if($shows){
            $data = [
                "total" => count($shows),
                "items" => $shows
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new AllShowsNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}