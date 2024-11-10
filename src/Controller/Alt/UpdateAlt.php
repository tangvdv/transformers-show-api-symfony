<?php
// src/Controller/Alt/UpdateAlt.php
namespace App\Controller\Alt;

use App\Normalizer\Alt\CreateUpdateAltNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class UpdateAlt extends AltController
{
    #[Route(
        '/api/alt/{id}',
        name: 'update_alt',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $alt = $this->altRepository->findOneWithParams(array("id" => $id));

        if(!$alt){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Alt not found");
        }

        $payload = $request->getPayload();
        $params = [
            "name" => [
                "value" =>  $payload->get("name"),
                "type" => "string",
                "nullable" => true
            ],
            "image" => [
                "value" => $payload->get("image"),
                "type" => "string",
                "nullable" => true,
                "method" => "setImage"
            ],
            "brand" => [
                "value" => $payload->get("brand"),
                "type" => "string",
                "nullable" => true,
                "method" => "setBrand"
            ],
            "model_year" => [
                "value" =>  $payload->get("model_year"),
                "type" => "integer",
                "nullable" => true,
                "method" => "setModelYear"
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
                        $alt->$method($value["value"]);
                    }
                }
            }
        }

        if($params["name"]["value"] !== null){
            if($this->altRepository->findOneBy(array("alt_name" => $params["name"]["value"]))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "An Alt already exist with this name");
            }
            else{
                $alt->setAltName($params["name"]["value"]);
            }
        }
    
        $entityManager->persist($alt);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $alt], [new CreateUpdateAltNormalizer]);
    }
}