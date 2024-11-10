<?php
// src/Controller/Entity/UpdateEntity.php
namespace App\Controller\Entity;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Normalizer\Entity\EntityNormalizer;
use Symfony\Component\ExpressionLanguage\Expression;

class UpdateEntity extends EntityController
{
    #[Route(
        '/api/entities/{id}',
        name: 'update_entity',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $entity = $this->entityRepository->findOneBy(array("id" => $id));

        if(!$entity){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
        }
        $payload = $request->getPayload();
        $params = [
            "name" => [
                "value" =>  $payload->get("name"),
                "default" => $entity->getEntityName(),
                "type" => "string",
                "nullable" => true
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

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Parameter `{$key}` is missing");
                }
                else{
                    if(array_key_exists("default", $value)){
                        $value["value"] = $value["default"];
                    }
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


        if($params["name"]["value"] !== $params["name"]["default"]){
            if($this->entityRepository->findOneBy(array("entity_name" => $params["name"]["value"]))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An Entity with this name already exist");
            }
            $entity->setEntityName($params["name"]["value"]);
        }
    
        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $entity], [new EntityNormalizer]);
    }
}