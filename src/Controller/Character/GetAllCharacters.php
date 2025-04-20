<?php
// src/Controller/Character/GetAllCharacters.php
namespace App\Controller\Character;

use App\Controller\Entity\EntityController;
use App\Normalizer\Character\CharacterCompactedNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Character\CharacterNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetAllCharacters extends EntityController
{
    #[Route(
        '/api/characters',
        name: 'get_characters',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') or is_granted('ROLE_APPLICATION')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request): Response
    {
        $limit = 10;
        $type = null;
        $compacted = false;

        if($request->query->get('limit') !== null && !empty($request->query->get('limit'))){
            if(filter_var($request->query->get('limit'), FILTER_VALIDATE_INT)){
                $limit = $request->query->getInt('limit');
            }
            else{
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `limit` is in incorrect type format, `integer` is needed");
            }
        }

        if($request->query->get('type') !== null && !empty($request->query->get('type'))){
            $type = filter_var($request->query->get('type'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        if($request->query->get('compacted') !== null && !empty($request->query->get('compacted'))){
            $compacted = filter_var($request->query->get('compacted'), FILTER_VALIDATE_BOOL);
        }

        $entities = $this->entityRepository->findAllWithParams($limit);

        if(!$compacted){
            $characters = new ArrayCollection();
            $normalizers = [new CharacterNormalizer];
            foreach($entities as $entity){
                if($type === null || $type === "bot"){
                    foreach($entity->getBots() as $bot){
                        $characters->add($bot);
                    }
                }

                if($type === null || $type === "human"){
                    foreach($entity->getHumans() as $human){
                        $characters->add($human);
                    }
                }
            }
        }
        else{
            $characters = $entities;
            $normalizers = [new CharacterCompactedNormalizer]; 
        }

        if($characters){
            $data = [
                "total" => count($characters),
                "limit" => $limit,
                "items" => $characters
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, $normalizers);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}