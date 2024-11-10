<?php
// src/Controller/Actor/GetAllActors.php
namespace App\Controller\Actor;

use App\Normalizer\Actor\ActorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllActors extends ActorController
{
    #[Route(
        '/api/actors',
        name: 'get_actors',
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
        
        $actors = $this->actorRepository->findAllWithParams($limit, $show);

        if($actors){
            $data = [
                "total" => count($actors),
                "limit" => $limit,
                "items" => $actors
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new ActorNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}