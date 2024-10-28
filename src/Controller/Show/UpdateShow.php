<?php
// src/Controller/Show/UpdateShow.php
namespace App\Controller\Show;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Normalizer\Show\CreateUpdateShowNormalizer;

class UpdateShow extends ShowController
{
    #[Route(
        '/api/shows/{id}',
        name: 'update_show',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $show = $this->showRepository->findOneBy(array("id" => $id));

        if(!$show){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        $payload = $request->getPayload();
        $params = [
            "name" => [
                "value" => $payload->get("name"),
                "type" => "string",
                "nullable" => true
            ],
            "description" => [
                "value" =>  $payload->get("description"),
                "type" => "string",
                "nullable" => true,
                "method" => "setDescription"
            ],
            "release_date" => [
                "value" => $payload->get("release_date"),
                "type" => "string",
                "nullable" => true,
                "method" => "setReleaseDate"
            ],
            "image" => [
                "value" =>  $payload->get("image"),
                "type" => "string",
                "nullable" => true,
                "method" => "setImage"
            ],
            "running_time" => [
                "value" =>  $payload->get("running_time"),
                "type" => "integer",
                "nullable" => true,
                "method" => "setRunningTime"
            ],
            "budget" => [
                "value" =>  $payload->get("budget"),
                "type" => "integer",
                "nullable" => true,
                "method" => "setBudget"
            ],
            "box_office" => [
                "value" => $payload->get("box_office"),
                "type" => "integer",
                "nullable" => true,
                "method" => "setBoxOffice"
            ]
        ];

        foreach($params as $key => &$value){
            if(!empty($value["value"])){
                if(gettype($value["value"]) != $value["type"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
                }
                else{
                    if($value["type"] === "string"){
                        $value["value"] = preg_replace('/\s+/','', $value["value"]);
                    }
                    if(array_key_exists("method", $value)){
                        $method = $value["method"];
                        $show->$method($value["value"]);
                    }
                } 
            }
        }

        if($params["name"]["value"] !== null){
            if($this->showRepository->findOneBy(array("show_name" => $params["name"]["value"]))){
                return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "A Show with this name already exist");
            }
        }

        $entityManager->persist($show);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $show], [new CreateUpdateShowNormalizer]);
    }
}