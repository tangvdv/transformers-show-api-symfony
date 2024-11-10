<?php
// src/Controller/Show/GetAllShows.php
namespace App\Controller\Show;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Show\AllShowsNormalizer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllShows extends ShowController
{
    #[Route(
        '/api/shows',
        name: 'get_shows',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
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