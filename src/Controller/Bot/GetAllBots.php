<?php
// src/Controller/Bot/GetAllBots.php
namespace App\Controller\Bot;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Bot\AllBotsNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllBots extends BotController
{
    #[Route(
        '/api/bots',
        name: 'get_bots',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request): Response
    {
        $limit = 10;
        $alt = null;
        $faction = null;

        if($request->query->get('limit') !== null && !empty($request->query->get('limit'))){
            if(filter_var($request->query->get('limit'), FILTER_VALIDATE_INT)){
                $limit = $request->query->getInt('limit');
            }
            else{
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `limit` is in incorrect type format, `integer` is needed");
            }
        }

        if($request->query->get('alt') !== null && !empty($request->query->get('alt'))){
            $alt = filter_var($request->query->get('alt'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        if($request->query->get('faction') !== null && !empty($request->query->get('faction'))){
            $faction = filter_var($request->query->get('faction'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        $bots = $this->botRepository->findAllWithParams($limit, $alt, $faction);

        if($bots){
            $data = [
                "total" => count($bots),
                "limit" => $limit,
                "items" => $bots
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new AllBotsNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}