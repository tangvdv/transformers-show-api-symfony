<?php
// src/Controller/ConceptArt/CreateConceptArt.php
namespace App\Controller\ConceptArt;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ConceptArt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\EntityRepository;
use App\Repository\ShowRepository;
use App\Normalizer\ConceptArt\CreateUpdateConceptArtNormalizer;

class CreateConceptArt extends ConceptArtController
{
    #[Route(
        '/api/conceptarts',
        name: 'create_conceptart',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager, EntityRepository $entityRepository, ShowRepository $showRepository): Response
    {
        $payload = $request->getPayload();
        $params = [
            "entityId" => [
                "value" =>  $payload->get("entityId"),
                "type" => "integer",
                "nullable" => false
            ],
            "showId" => [
                "value" => $payload->get("showId"),
                "type" => "integer",
                "nullable" => false
            ],
            "title" => [
                "value" => $payload->get("title"),
                "type" => "string",
                "nullable" => false
            ],
            "image" => [
                "value" => $payload->get("image"),
                "type" => "string",
                "nullable" => false
            ],
            "note" => [
                "value" => $payload->get("note"),
                "type" => "string",
                "nullable" => true,
                "method" => "setNote"
            ],
            "srclink" => [
                "value" =>  $payload->get("srclink"),
                "type" => "string",
                "nullable" => true,
                "method" => "setSrcLink"
            ],
            "date" => [
                "value" =>  $payload->get("date"),
                "type" => "string",
                "nullable" => true,
                "method" => "setDate"
            ]
        ];

        $conceptart = new ConceptArt();

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
                        $conceptart->$method($value["value"]);
                    }
                }
            }
        }
        
        $entity = $entityRepository->find($params["entityId"]["value"]);
        if($entity === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Entity not found");
        }
        
        $show = $showRepository->find($params["showId"]["value"]);
        if($show === null){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        $conceptart
            ->setTitle($params["title"]["value"])
            ->setImage($params["image"]["value"])
            ->setEntity($entity)
            ->setShow($show);

        $entityManager->persist($conceptart);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $conceptart], [new CreateUpdateConceptArtNormalizer]);
    }
}