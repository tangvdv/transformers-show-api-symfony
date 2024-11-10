<?php
// src/Controller/Creator/CreateCreator.php
namespace App\Controller\Creator;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Creator;
use App\Normalizer\Creator\CreateUpdateCreatorNormalizer;
use Symfony\Component\ExpressionLanguage\Expression;

class CreateCreator extends CreatorController
{
    #[Route(
        '/api/creators',
        name: 'create_creator',
        methods: ['POST']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payload = $request->getPayload();

        $categories = ["producer", "writer", "composer", "director"];

        $params = [
            "first_name" => [
                "value" =>  $payload->get("first_name"),
                "type" => "string",
                "nullable" => false
            ],
            "last_name" => [
                "value" => $payload->get("last_name"),
                "type" => "string",
                "nullable" => false
            ],
            "category" => [
                "value" =>  $payload->get("category"),
                "type" => "string",
                "nullable" => false
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
            }
        }

        if(!in_array($params["category"]["value"], $categories)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `category` has incorrect value. `producer`, `director`, `writer` or `composer` is authorized");
        }

        if($this->creatorRepository->findOneBy(
            array(
                "creator_firstname" => $params["first_name"]["value"], 
                "creator_lastname" => $params["last_name"]["value"]
        ))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "A creator with this name already exist");
        }

        $creator = new Creator();
        $creator
            ->setCreatorFirstname($params["first_name"]["value"])
            ->setCreatorLastname($params["last_name"]["value"])
            ->setCategory($params["category"]["value"]);
        $entityManager->persist($creator);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $creator], [new CreateUpdateCreatorNormalizer]);
    }
}