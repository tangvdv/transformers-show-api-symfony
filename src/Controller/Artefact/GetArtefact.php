<?php
// src/Controller/Artefact/GetArtefact.php
namespace App\Controller\Artefact;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Artefact\ArtefactNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetArtefact extends ArtefactController
{
    #[Route(
        '/api/artefacts/{id}',
        name: 'get_artefact_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getArtefactByID(int $id): Response
    {
        $artefact = $this->artefactRepository->findOneWithParams(array("id" => $id));
        return $this->response($artefact);
    }

    #[Route(
        '/api/artefacts/{name}',
        name: 'get_artefact_name',
        methods: ['GET'],
        requirements: ['name' => '\w+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function getArtefactByName(string $name, Request $request): Response
    {
        $show = null;
        if($request->query->get('show') !== null && !empty($request->query->get('show'))){
            $show = filter_var($request->query->get('show'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        $artefact = $this->artefactRepository->findOneWithParams(array("name" => $name, "show" => $show));
        return $this->response($artefact);
    }

    private function response(mixed $artefact): Response
    {
        if($artefact){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $artefact], [new ArtefactNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artefact not found");
        }
    }
}