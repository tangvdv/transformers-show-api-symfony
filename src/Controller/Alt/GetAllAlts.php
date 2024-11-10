<?php
// src/Controller/Alt/GetAllAlts.php
namespace App\Controller\Alt;

use App\Normalizer\Alt\AltNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\ResponseHandler;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllAlts extends AltController
{
    #[Route(
        '/api/alts',
        name: 'get_alts',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, ResponseHandler $responseHandler): Response
    {
        $limit = 10;
        $bot = null;

        if($request->query->get('limit') !== null && !empty($request->query->get('limit'))){
            if(filter_var($request->query->get('limit'), FILTER_VALIDATE_INT)){
                $limit = $request->query->getInt('limit');
            }
            else{
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `limit` is in incorrect type format, `integer` is needed");
            }
        }

        if($request->query->get('bot') !== null && !empty($request->query->get('bot'))){
            $bot = filter_var($request->query->get('bot'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        $alts = $this->altRepository->findAllWithParams($limit, $bot);

        if($alts){
            $data = [
                "total" => count($alts),
                "limit" => $limit,
                "items" => $alts
            ];
            return $responseHandler->createResponse(Response::HTTP_OK, $data, [new AltNormalizer]);
        }
        else{
            return $responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}