<?php
// src/Controller/VoiceActor/GetAllVoiceActors.php
namespace App\Controller\VoiceActor;

use App\Normalizer\VoiceActor\VoiceActorNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllVoiceActors extends VoiceActorController
{
    #[Route(
        '/api/voiceactors',
        name: 'get_voice_actors',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request): Response
    {
        $limit = 10;
        $show = null;
        $bot = null;

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

        if($request->query->get('bot') !== null && !empty($request->query->get('show'))){
            $show = filter_var($request->query->get('show'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        $voiceactors = $this->voiceactorRepository->findAllWithParams($limit, $show, $bot);

        if($voiceactors){
            $data = [
                "total" => count($voiceactors),
                "limit" => $limit,
                "items" => $voiceactors
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new VoiceActorNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_OK);
        }
    }
}