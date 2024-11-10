<?php
// src/Controller/Artefact/GetAllArtefacts.php
namespace App\Controller\Artefact;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Normalizer\Artefact\ArtefactNormalizer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllArtefacts extends ArtefactController
{
    #[Route(
        '/api/artefacts',
        name: 'get_artefacts',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request): Response
    {
        $limit = 10;
        $show = null;

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
        
        $artefacts = $this->artefactRepository->findAllWithParams($limit, $show);

        if($artefacts){
            $data = [
                "total" => count($artefacts),
                "limit" => $limit,
                "items" => $artefacts
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new ArtefactNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}