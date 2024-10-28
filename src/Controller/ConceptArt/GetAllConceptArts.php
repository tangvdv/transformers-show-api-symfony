<?php
// src/Controller/ConceptArt/GetAllConceptArts.php
namespace App\Controller\ConceptArt;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Normalizer\ConceptArt\ConceptArtNormalizer;

class GetAllConceptArts extends ConceptArtController
{
    #[Route(
        '/api/conceptarts',
        name: 'get_conceptarts',
        methods: ['GET']
    )]
    public function __invoke(Request $request): Response
    {
        $limit = 10;
        $show = null;
        $artist = null;
        $entity = null;

        if($request->query->get('limit') !== null && !empty($request->query->get('limit'))){
            if(filter_var($request->query->get('limit'), FILTER_VALIDATE_INT)){
                $limit = $request->query->getInt('limit');
            }
            else{
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `limit` is in incorrect type format, `integer` is needed");
            }
        }

        if($request->query->get('show') !== null && !empty($request->query->get('show'))){
            $show = filter_var($request->query->get('show'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        if($request->query->get('entity') !== null && !empty($request->query->get('entity'))){
            $entity = filter_var($request->query->get('entity'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        if($request->query->get('artist') !== null && !empty($request->query->get('artist'))){
            $artist = filter_var($request->query->get('artist'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        $conceptArts = $this->conceptArtRepository->findAllWithParams($limit, $show, $entity, $artist);

        if($conceptArts){
            $data = [
                "total" => count($conceptArts),
                "limit" => $limit,
                "items" => $conceptArts
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new ConceptArtNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_OK);
        }
    }
}