<?php
// src/Controller/Entity/GetAllEntities.php
namespace App\Controller\Entity;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Normalizer\Entity\EntityNormalizer;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetAllEntities extends EntityController
{
    #[Route(
        '/api/entities',
        name: 'get_entities',
        methods: ['GET']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request): Response
    {
        $limit = 10;

        if($request->query->get('limit') !== null && !empty($request->query->get('limit'))){
            if(filter_var($request->query->get('limit'), FILTER_VALIDATE_INT)){
                $limit = $request->query->getInt('limit');
            }
            else{
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `limit` is in incorrect type format, `integer` is needed");
            }
        }

        $entities = $this->entityRepository->findAllWithParams($limit);

        if($entities){
            $data = [
                "total" => count($entities),
                "limit" => $limit,
                "items" => $entities
            ];
            return $this->responseHandler->createResponse(Response::HTTP_OK, $data, [new EntityNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}