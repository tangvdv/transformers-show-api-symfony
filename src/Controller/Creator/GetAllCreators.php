<?php
// src/Controller/Creator/GetAllCreators.php
namespace App\Controller\Creator;

use App\Normalizer\Creator\CreatorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllCreators extends CreatorController
{
    #[Route(
        '/api/creators',
        name: 'get_creators',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request): Response
    {
        $limit = 10;
        $show = null;
        $category = null;


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

        if($request->query->get('category') !== null && !empty($request->query->get('category'))){
            $category = filter_var($request->query->get('category'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        $creators = $this->creatorRepository->findAllWithParams($limit, $show, $category);

        if($creators){
            $data = [
                "total" => count($creators),
                "limit" => $limit,
                "items" => $creators
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new CreatorNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}