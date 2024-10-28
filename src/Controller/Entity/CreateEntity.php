<?php
// src/Controller/Entity/CreateEntity.php
namespace App\Controller\Entity;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Normalizer\Entity\EntityNormalizer;

class CreateEntity extends EntityController
{
    #[Route(
        '/api/entity',
        name: 'create_entity',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payload = $request->getPayload();
        $params = [
            "name" => [
                "value" =>  $payload->get("name"),
                "type" => "string",
                "nullable" => false
            ],
            "image" => [
                "value" => $payload->get("image"),
                "type" => "string",
                "nullable" => true,
                "method" => "setImage"
            ],
            "type" => [
                "value" => $payload->get("type"),
                "type" => "integer",
                "nullable" => true,
                "method" => "setType"
            ]
        ];

        $entity = new Entity();

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Parameter `{$key}` is missing");
                }
            }
            else{
                if(gettype($value["value"]) != $value["type"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
                }
                else{
                    if(array_key_exists("method", $value)){
                        $method = $value["method"];
                        $entity->$method($value["value"]);
                    }
                }
            }
        }

        if($this->entityRepository->findOneBy(array("entity_name" => $params["name"]["value"]))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An Entity with this name already exist");
        }

        $entity->setEntityName($params["name"]["value"]);

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $entity], [new EntityNormalizer]);
    }
}