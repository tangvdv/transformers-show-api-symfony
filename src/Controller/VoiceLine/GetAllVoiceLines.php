<?php
// src/Controller/VoiceLine/GetAllVoiceLines.php
namespace App\Controller\VoiceLine;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Normalizer\VoiceLine\VoiceLineNormalizer;

class GetAllVoiceLines extends VoiceLineController
{
    #[Route(
        '/api/voicelines',
        name: 'get_voice_lines',
        methods: ['GET']
    )]
    public function __invoke(Request $request): Response
    {
        $limit = 10;
        $show = null;
        $entity = null;

        if($request->query->get('limit') !== null && !empty($request->query->get('limit'))){
            if(filter_var($request->query->get('limit'), FILTER_VALIDATE_INT)){
                $limit = $request->query->getInt('limit');
            }
            else{
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST,"Parameter `limit` is in incorrect type format, `integer` is needed");
            }
        }

        if($request->query->get('show') !== null && !empty($request->query->get('show'))){
            $show = filter_var($request->query->get('show'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        if($request->query->get('entity') !== null && !empty($request->query->get('entity'))){
            $entity = filter_var($request->query->get('entity'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        $voicelines = $this->voiceLineRepository->findAllWithParams($limit, $show, $entity);

        if($voicelines){
            $data = [
                "total" => count($voicelines),
                "limit" => $limit,
                "items" => $voicelines
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new VoiceLineNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}