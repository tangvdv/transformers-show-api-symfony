<?php
// src/Controller/ConceptArt/GetConceptArt.php
namespace App\Controller\ConceptArt;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\ConceptArt\ConceptArtNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetConceptArt extends ConceptArtController
{
    #[Route(
        '/api/conceptarts/{id}',
        name: 'get_concept_art_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getConceptArtByID(int $id): Response
    {
        $conceptart = $this->conceptArtRepository->findOneWithParams(array("id" => $id));
        return $this->response($conceptart);
    }

    #[Route(
        '/api/conceptarts/{title}',
        name: 'get_concept_art_name',
        methods: ['GET'],
        requirements: ['title' => '\w+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getConceptArtByName(string $title, Request $request): Response
    {
        $show = null;
        if($request->query->get('show') !== null && !empty($request->query->get('show'))){
            $show = filter_var($request->query->get('show'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        $entity = null;
        if($request->query->get('entity') !== null && !empty($request->query->get('entity'))){
            $entity = filter_var($request->query->get('entity'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        $artist = null;
        if($request->query->get('artist') !== null && !empty($request->query->get('artist'))){
            $artist = filter_var($request->query->get('artist'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        $conceptart = $this->conceptArtRepository->findOneWithParams(array("title" => $title, "show" => $show, "entity" => $entity, "artist" => $artist));
        return $this->response($conceptart);
    }

    private function response(mixed $conceptart): Response
    {
        if($conceptart){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $conceptart], [new ConceptArtNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Concept Art not found");
        }
    }
}